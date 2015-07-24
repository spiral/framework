<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Selector;
use Spiral\Components\ORM\Selector\Loader;

class ManyToManyLoader extends Loader
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = ActiveRecord::MANY_TO_MANY;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = Selector::POSTLOAD;

    /**
     * Internal loader constant used to decide nested aggregation level.
     */
    const MULTIPLE = true;

    /**
     * Set of pivot table columns has to be fetched from resulted query.
     *
     * @var array
     */
    protected $pivotColumns = [];

    /**
     * Pivot columns offset in resulted query row.
     *
     * @var int
     */
    protected $pivotColumnsOffset = 0;

    /**
     * New instance of ORM Loader. Loader can always load additional components using
     * ORM->getContainer().
     *
     * @param ORM    $orm
     * @param string $container  Location in parent loaded where data should be attached.
     * @param array  $definition Definition compiled by relation relation schema and stored in ORM
     *                           cache.
     * @param Loader $parent     Parent loader if presented.
     */
    public function __construct(ORM $orm, $container, array $definition = [], Loader $parent = null)
    {
        parent::__construct($orm, $container, $definition, $parent);
        $this->pivotColumns = $this->definition[ActiveRecord::PIVOT_COLUMNS];
    }

    /**
     * Pivot table name.
     *
     * @return string
     */
    public function getPivotTable()
    {
        return $this->definition[ActiveRecord::PIVOT_TABLE];
    }

    /**
     * Pivot table alias depends on relation table alias.
     *
     * @return string
     */
    public function getPivotAlias()
    {
        if (!empty($this->options['pivotAlias']))
        {
            return $this->options['pivotAlias'];
        }

        return $this->getAlias() . '_pivot';
    }

    /**
     * Key related to pivot table.
     *
     * @param string $key
     * @return null|string
     */
    public function getPivotKey($key)
    {
        if (!isset($this->definition[$key]))
        {
            return null;
        }

        return $this->getPivotAlias() . '.' . $this->definition[$key];
    }

    /**
     * Configure columns required for loader data selection.
     *
     * @param Selector $selector
     */
    protected function configureColumns(Selector $selector)
    {
        if (!$this->isLoaded())
        {
            return;
        }

        $this->columnsOffset = $selector->registerColumns(
            $this->getAlias(),
            $this->columns
        );

        $this->pivotColumnsOffset = $selector->registerColumns(
            $this->getPivotAlias(),
            $this->pivotColumns
        );
    }

    /**
     * Create selector to be executed as post load, usually such selector use aggregated values
     * and IN where syntax.
     *
     * @param string $parentRole
     * @return Selector
     */
    public function createSelector($parentRole = '')
    {
        if (empty($selector = parent::createSelector()))
        {
            return null;
        }

        //Pivot table joining (INNER)
        $pivotOuterKey = $this->getPivotKey(ActiveRecord::THOUGHT_OUTER_KEY);
        $selector->innerJoin($this->getPivotTable() . ' AS ' . $this->getPivotAlias(), [
            $pivotOuterKey => $this->getKey(ActiveRecord::OUTER_KEY)
        ]);

        $this->mountPivotConditions($selector, $parentRole);

        if (empty($this->parent))
        {
            return $selector;
        }

        $this->mountConditions($selector);

        if (empty($this->parent))
        {
            //For Many-To-Many loader
            return $selector;
        }

        //Aggregated keys (example: all parent ids)
        if (empty($aggregatedKeys = $this->parent->getAggregatedKeys($this->getReferenceKey())))
        {
            //Nothing to postload, no parents
            return null;
        }

        //Adding condition
        $selector->where($this->getPivotKey(ActiveRecord::THOUGHT_INNER_KEY), 'IN', $aggregatedKeys);

        return $selector;
    }

    /**
     * ORM Loader specific method used to clarify selector conditions, join and columns with
     * loader specific information.
     *
     * @param Selector $selector
     */
    protected function clarifySelector(Selector $selector)
    {
        $selector->join($this->joinType(), $this->getPivotTable() . ' AS ' . $this->getPivotAlias(), [
            $this->getPivotKey(ActiveRecord::THOUGHT_INNER_KEY) => $this->getParentKey()
        ]);

        $this->mountPivotConditions($selector);

        $pivotOuterKey = $this->getPivotKey(ActiveRecord::THOUGHT_OUTER_KEY);
        $selector->join($this->joinType(), $this->getTable() . ' AS ' . $this->getAlias(), [
            $pivotOuterKey => $this->getKey(ActiveRecord::OUTER_KEY)
        ]);

        $this->mountConditions($selector);
    }

    /**
     * Mounting pivot table conditions including user defined and morph key.
     *
     * @param Selector $selector
     * @param string   $parentRole
     * @return Selector
     */
    protected function mountPivotConditions(Selector $selector, $parentRole = '')
    {
        //We have to route all conditions to ON statement
        $router = new Selector\WhereDecorator($selector, 'onWhere', $this->getPivotAlias());

        if (!empty($morphKey = $this->getPivotKey(ActiveRecord::MORPH_KEY)))
        {
            $router->where(
                $morphKey,
                !empty($parentRole) ? $parentRole : $this->parent->schema[ORM::E_ROLE_NAME]
            );
        }

        if (!empty($this->definition[ActiveRecord::WHERE_PIVOT]))
        {
            //Relation WHERE conditions
            $router->where($this->definition[ActiveRecord::WHERE_PIVOT]);
        }

        //User specified WHERE conditions
        !empty($this->options['wherePivot']) && $router->where($this->options['wherePivot']);
    }

    /**
     * Set relational and user conditions.
     *
     * @param Selector $selector
     * @return Selector
     */
    protected function mountConditions(Selector $selector)
    {
        //Let's use where decorator to set conditions, it will automatically route tokens to valid
        //destination (JOIN or WHERE)
        $router = new Selector\WhereDecorator(
            $selector,
            $this->isJoined() ? 'onWhere' : 'where',
            $this->getAlias()
        );

        if (!empty($this->definition[ActiveRecord::WHERE]))
        {
            //Relation WHERE conditions
            $router->where($this->definition[ActiveRecord::WHERE]);
        }

        //User specified WHERE conditions
        !empty($this->options['where']) && $router->where($this->options['where']);
    }

    /**
     * Helper method used to fetch named pivot table fields from query result, will automatically
     * calculate data offset and resolve field aliases.
     *
     * @param array $row
     * @return array
     */
    protected function fetchData(array $row)
    {
        $data = parent::fetchData($row);

        $data[ORM::PIVOT_DATA] = array_combine(
            $this->pivotColumns,
            array_slice($row, $this->pivotColumnsOffset, count($this->pivotColumns))
        );

        return $data;
    }

    /**
     * Fetch criteria (value) to be used for data construction. Usually this value points to OUTER_KEY
     * of relation.
     *
     * ManyToMany criteria located in pivot table and declared by different key type.
     *
     * @see getReferenceKey()
     * @param array $data
     * @return mixed
     */
    public function fetchReferenceCriteria(array $data)
    {
        if (!isset($data[ORM::PIVOT_DATA][$this->definition[ActiveRecord::THOUGHT_INNER_KEY]]))
        {
            return null;
        }

        return $data[ORM::PIVOT_DATA][$this->definition[ActiveRecord::THOUGHT_INNER_KEY]];
    }

    /**
     * In many cases (for example if you have inload of HAS_MANY relation) model data can be spreaded
     * by many result rows (duplicated). To prevent wrong data linking we have to deduplicate such
     * records.
     *
     * Method will return true if data wasn't handled before and this is first occurence and false
     * in opposite case.
     *
     * @param array $data                   Reference to parsed record, reference will be pointed to
     *                                      valid and existed data segment if such data was already
     *                                      parsed.
     * @return bool
     */
    protected function deduplicate(array &$data)
    {
        $criteria = $data[ORM::PIVOT_DATA][$this->definition[ActiveRecord::THOUGHT_INNER_KEY]]
            . '.' . $data[ORM::PIVOT_DATA][$this->definition[ActiveRecord::THOUGHT_OUTER_KEY]];

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $criteria .= ':' . $data[ORM::PIVOT_DATA][$this->definition[ActiveRecord::MORPH_KEY]];
        }

        if (isset($this->duplicates[$criteria]))
        {
            //Duplicate is presented, let's reduplicate
            $data = $this->duplicates[$criteria];

            //Duplicate is presented
            return false;
        }

        //Let's remember record to prevent future duplicates
        $this->duplicates[$criteria] = &$data;

        return true;
    }
}