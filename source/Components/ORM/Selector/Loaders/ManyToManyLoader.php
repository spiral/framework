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
use Spiral\Components\ORM\Relation;
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
     * New instance of ORM loader.
     *
     * @param ORM    $orm
     * @param string $container
     * @param array  $definition
     * @param Loader $parent
     */
    public function __construct(
        ORM $orm,
        $container,
        array $definition = [],
        Loader $parent
    )
    {
        parent::__construct($orm, $container, $definition, $parent);
        $this->pivotColumns = $this->definition[ActiveRecord::PIVOT_COLUMNS];
    }

    /**
     * Pivot table name.
     *
     * @return string
     */
    protected function getPivotTable()
    {
        return $this->definition[ActiveRecord::PIVOT_TABLE];
    }

    /**
     * Pivot table alias depends on relation table alias.
     *
     * @return string
     */
    protected function getPivotAlias()
    {
        return $this->getAlias() . '_pivot';
    }

    /**
     * Configure columns required for loader data selection.
     *
     * @param Selector $selector
     */
    protected function configureColumns(Selector $selector)
    {
        if (!$this->isLoadable())
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
     * @return Selector
     */
    public function createSelector()
    {
        if (empty($selector = parent::createSelector()))
        {
            return null;
        }

        //Aggregated keys (example: all parent ids)
        $aggregatedKeys = $this->parent->getAggregatedKeys($this->getReferenceKey());

        if (empty($aggregatedKeys))
        {
            //Nothing to postload, no parents
            return null;
        }

        $pivotTable = $this->definition[ActiveRecord::PIVOT_TABLE];

        $outerKey = $this->getAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY];
        $pivotOuterKey = $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_OUTER_KEY];

        //Joining map table
        $selector->join($pivotTable . ' AS ' . $this->getPivotAlias(), [
            $pivotOuterKey => $outerKey
        ]);

        //Adding condition
        $selector->where(
            $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
            'IN',
            array_unique($aggregatedKeys)
        );

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::MORPH_KEY];
            $selector->where([$morphKey => $this->parent->schema[ORM::E_ROLE_NAME]]);
        }

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
        $pivotTable = $this->definition[ActiveRecord::PIVOT_TABLE];

        $outerKey = $this->getAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY];
        $innerKey = $this->parent->getAlias() . '.' . $this->definition[ActiveRecord::INNER_KEY];

        $pivotOuterKey = $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_OUTER_KEY];
        $pivotInnerKey = $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_INNER_KEY];

        $selector->leftJoin($pivotTable . ' AS ' . $this->getPivotAlias(), [
            $pivotInnerKey => $innerKey
        ]);

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getPivotAlias() . '.' . $this->definition[ActiveRecord::MORPH_KEY];
            $selector->onWhere([$morphKey => $this->parent->schema[ORM::E_ROLE_NAME]]);
        }

        $selector->leftJoin(
            $this->definition[Relation::OUTER_TABLE] . ' AS ' . $this->getAlias(), [
                $outerKey => $pivotOuterKey
            ]
        );
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
     * Parse single result row, should fetch related model fields and run nested loader parsers.
     *
     * @param array $row
     * @return mixed
     */
    public function parseRow(array $row)
    {
        if (!$this->isLoadable())
        {
            return;
        }

        $data = $this->fetchData($row);

        if (!$referenceCriteria = $this->fetchReferenceCriteria($data))
        {
            //Relation not loaded
            return;
        }

        //We have to check deduplication based on pivot table data
        if ($unique = $this->deduplicate($data))
        {
            //Clarifying parent dataset
            $this->collectReferences($data);
        }

        if ($this->options['method'] == Selector::INLOAD)
        {
            $this->parent->mount(
                $this->container,
                $this->getReferenceKey(),
                $referenceCriteria,
                $data,
                true
            );
        }
        else
        {
            $this->parent->mountOuter(
                $this->container,
                $this->getReferenceKey(),
                $referenceCriteria,
                $data,
                true
            );
        }

        $this->parseNested($row);
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
        $criteria = $data[ORM::PIVOT_DATA][ORM::PIVOT_PRIMARY_KEY];
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

    /**
     * Mount model data to parent loader under specified container, using reference key (inner key)
     * and reference criteria (outer key value).
     *
     * Example:
     * $this->parent->mount('profile', 'id', 1, [
     *      'id' => 100,
     *      'user_id' => 1,
     *      ...
     * ]);
     *
     * In this example "id" argument is inner key of "user" model and it's linked to outer key
     * "user_id" in "profile" model, which defines reference criteria as 1.
     *
     * @param string $container
     * @param string $key
     * @param mixed  $criteria
     * @param array  $data
     * @param bool   $multiple If true all mounted records will added to array.
     */
    public function mount(
        $container,
        $key,
        $criteria,
        array &$data,
        $multiple = false
    )
    {
        $this->mountOuter($container, $key, $criteria, $data, $multiple);
    }
}