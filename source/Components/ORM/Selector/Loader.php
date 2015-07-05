<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\QueryResult;
use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Selector;

abstract class Loader implements LoaderInterface
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = null;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = null;

    /**
     * Internal loader constant used to decide nested aggregation level.
     */
    const MULTIPLE = false;

    /**
     * ORM component is required to fetch all required model schemas.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    /**
     * Container related to parent loader.
     *
     * @var string
     */
    protected $container = '';

    /**
     * Relation definition options.
     *
     * @var array
     */
    protected $definition = [];

    /**
     * Parent loader.
     *
     * @invisible
     * @var Loader|null
     */
    protected $parent = null;

    /**
     * Related model schema.
     *
     * @invisible
     * @var array
     */
    protected $schema = [];

    /**
     * Loader options usually used while declaring joins and etc.
     *
     * @var array
     */
    protected $options = [
        'method' => null,
        'alias' => null,
        'load'  => true
    ];

    /**
     * Loader already configured selector, no need to do it twice.
     *
     * @var bool
     */
    protected $configured = false;

    /**
     * Set of columns has to be fetched from resulted query.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Columns offset in resulted query row.
     *
     * @var int
     */
    protected $columnsOffset = 0;

    /**
     * Inner loaders.
     *
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * Helper structure used to prevent data duplication when LEFT JOIN multiplies parent records.
     *
     * @invisible
     * @var array
     */
    protected $duplicates = [];

    /**
     * List of keys has to be stored as data references. This set of keys is required by inner loader(s)
     * to quickly compile nested data.
     *
     * @var array
     */
    protected $referenceKeys = [];

    /**
     * References aggregated by it's reference key and stored as multidimensional array.
     *
     * @var array
     */
    protected $references = [];

    /**
     * Result of data normalization.
     *
     * @var array
     */
    protected $result = [];

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
        $this->orm = $orm;

        $this->container = $container;
        $this->definition = $definition;
        $this->parent = $parent;

        //Related model schema
        $this->schema = $orm->getSchema($this->getTarget());

        //Compiling options
        $this->options['method'] = static::LOAD_METHOD;

        if (!empty($parent) && $parent->getDatabase() != $this->getDatabase())
        {
            //We have to force post-load if parent loader database is different
            $this->options['method'] = Selector::POSTLOAD;
        }

        if ($this->parent instanceof Selector\Loaders\RootLoader)
        {
            $this->options['alias'] = $container;
        }
        else
        {
            $this->options['alias'] = $this->parent->getAlias() . '_' . $container;
        }

        $this->columns = array_keys($this->schema[ORM::E_COLUMNS]);
    }

    /**
     * Get model class loader references to.
     *
     * @return string
     */
    protected function getTarget()
    {
        return $this->definition[static::RELATION_TYPE];
    }

    /**
     * Table name loader relates to.
     *
     * @return mixed
     */
    public function getTable()
    {
        return $this->schema[ORM::E_TABLE];
    }

    /**
     * Database name loader relates to.
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->schema[ORM::E_DB];
    }

    /**
     * Table alias to be used in query.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->options['alias'];
    }

    /**
     * Instance of Dbal\Database data should be loaded from.
     *
     * @return Database
     */
    public function dbalDatabase()
    {
        return $this->orm->getDBAL()->db($this->schema[ORM::E_DB]);
    }

    /**
     * Indicates that loader columns should be included into query statement. Used in cases
     * where relation is joined for conditional purposes only.
     *
     * @return bool
     */
    public function isLoadable()
    {
        if (!empty($this->parent) && !$this->parent->isLoadable())
        {
            return false;
        }

        return $this->options['load'];
    }

    /**
     * Update loader options.
     *
     * @param array $options
     * @return static
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * Receive loader options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @todo: rewrite
     * @param string $relation
     * @param array  $options
     * @return Loader
     */
    public function loader($relation, array $options = [], $forceOptions = [])
    {
        if (($position = strpos($relation, '.')) !== false)
        {
            $parentRelation = substr($relation, 0, $position);

            //Recursively
            return $this->loader($parentRelation, [], $forceOptions)->loader(
                substr($relation, $position + 1),
                $options,
                $forceOptions
            );
        }

        if (!isset($this->schema[ORM::E_RELATIONS][$relation]))
        {
            $container = $this->container ?: $this->schema[ORM::E_ROLE_NAME];

            throw new ORMException(
                "Undefined relation '{$relation}' under '{$container}'."
            );
        }

        if (isset($this->loaders[$relation]))
        {
            $this->loaders[$relation]->setOptions($forceOptions + $options);

            return $this->loaders[$relation];
        }

        $relationOptions = $this->schema[ORM::E_RELATIONS][$relation];

        $loader = $this->orm->relationLoader(
            $relationOptions[ORM::R_TYPE],
            $relation,
            $relationOptions[ORM::R_DEFINITION],
            $this
        );

        $loader->setOptions($forceOptions + $options);
        $this->loaders[$relation] = $loader;

        if ($referenceKey = $loader->getReferenceKey())
        {
            /**
             * Inner loader requested parent to pre-collect some keys so it can build tree using
             * references without looking up for correct record every time.
             */
            $this->referenceKeys[] = $referenceKey;
            $this->referenceKeys = array_unique($this->referenceKeys);
        }

        return $loader;
    }

    /**
     * Create selector to be executed as post load, usually such selector use aggregated values
     * and IN where syntax.
     *
     * @return Selector|null
     */
    public function createSelector()
    {
        if (!$this->isLoadable())
        {
            return null;
        }

        $selector = new Selector($this->definition[static::RELATION_TYPE], $this->orm, $this);
        $this->configureColumns($selector);

        foreach ($this->loaders as $loader)
        {
            $loader->configureSelector($selector);
        }

        return $selector;
    }

    /**
     * Clarify parent selection.
     *
     * @param Selector $selector
     */
    public function configureSelector(Selector $selector)
    {
        if ($this->options['method'] !== Selector::INLOAD)
        {
            return;
        }

        if (!$this->configured)
        {
            $this->configureColumns($selector);

            //Inload conditions and etc
            $this->clarifySelector($selector);

            $this->configured = true;
        }

        foreach ($this->loaders as $loader)
        {
            $loader->configureSelector($selector);
        }
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
    }

    /**
     * ORM Loader specific method used to clarify selector conditions, join and columns with
     * loader specific information.
     *
     * @param Selector $selector
     */
    abstract protected function clarifySelector(Selector $selector);

    /**
     * Run post selection queries to clarify fetched model data. Usually many conditions will be
     * fetched from there. Additionally this method may be used to create relations to external
     * source of data (ODM, elasticSearch and etc).
     */
    public function postLoad()
    {
        foreach ($this->loaders as $loader)
        {
            if ($loader instanceof Loader && $loader->options['method'] == Selector::POSTLOAD)
            {
                if (!empty($selector = $loader->createSelector()))
                {
                    //Data will be automatically linked via references
                    $selector->fetchData();
                }
            }
            else
            {
                $loader->postLoad();
            }
        }
    }

    /**
     * Parse provided query result to fetch model fields and resolve nested loaders.
     *
     * @param QueryResult $result
     * @param int         $rowsCount
     * @return array
     */
    public function parseResult(QueryResult $result, &$rowsCount)
    {
        foreach ($result as $row)
        {
            $this->parseRow($row);
            $rowsCount++;
        }

        return $this->result;
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

        if ($this->deduplicate($data))
        {
            //Clarifying parent dataset
            $this->collectReferences($data);
        }

        $this->parent->mount(
            $this->container,
            $this->getReferenceKey(),
            $referenceCriteria,
            $data,
            static::MULTIPLE
        );

        $this->parseNested($row);
    }

    /**
     * Send row data to nested loaders for parsing (used in cases where nested loaded requested
     * using INLOAD method).
     *
     * @param array $row
     */
    protected function parseNested(array $row)
    {
        foreach ($this->loaders as $loader)
        {
            if ($loader instanceof Loader && $loader->options['method'] == Selector::INLOAD)
            {
                $loader->parseRow($row);
            }
        }
    }

    /**
     * Get built loader result. Result data can be altered by nested loaders (inload and postload),
     * so we have to run all post loaders before using this method result.
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Helper method used to fetch named fields from query result, will automatically calculate data
     * offset and resolve field aliases.
     *
     * @param array $row
     * @return array
     */
    protected function fetchData(array $row)
    {
        return array_combine(
            $this->columns,
            array_slice($row, $this->columnsOffset, count($this->columns))
        );
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
        if (isset($this->schema[ORM::E_PRIMARY_KEY]))
        {
            //We can use model id as de-duplication criteria
            $criteria = $data[$this->schema[ORM::E_PRIMARY_KEY]];
        }
        else
        {
            //It is recommended to use primary keys in every model as it will speed up de-duplication.
            $criteria = serialize($data);
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
     * Reference key (from parent object) required to speed up data normalization. In most of cases
     * this is primary key of parent model.
     *
     * For example HAS_ONE relation will request parent to collect INNER_KEY as quick reference.
     *
     * @see fetchReferenceCriteria()
     * @return string
     */
    public function getReferenceKey()
    {
        //Fairly simple logic
        return $this->definition[ActiveRecord::INNER_KEY];
    }

    /**
     * Fetch criteria (value) to be used for data construction. Usually this value points to OUTER_KEY
     * of relation.
     *
     * @see getReferenceKey()
     * @param array $data
     * @return mixed
     */
    public function fetchReferenceCriteria(array $data)
    {
        if (!isset($data[$this->definition[ActiveRecord::OUTER_KEY]]))
        {
            return null;
        }

        return $data[$this->definition[ActiveRecord::OUTER_KEY]];
    }

    /**
     * Create internal references to structure segments based on requested keys. For example, if we
     * have request for "id" as reference key, every record will create following records:
     * $this->references[id][ID_VALUE] = ITEM
     *
     * Make sure you collecting references only on first record occurrence, make sure that
     * deduplicate() method result is true.
     *
     * @see deduplicate()
     * @param array $data
     */
    protected function collectReferences(array &$data)
    {
        foreach ($this->referenceKeys as $key)
        {
            //Adding reference(s)
            $this->references[$key][$data[$key]][] = &$data;
        }
    }

    /**
     * Get list of unique keys aggregated by loader while data parsing. This list used by sub-loaders
     * in situations where data has to be loader with POSTLOAD method (usually this value will go
     * directly to WHERE IN statement).
     *
     * @param string $referenceKey
     * @return array
     */
    public function getAggregatedKeys($referenceKey)
    {
        if (!isset($this->references[$referenceKey]))
        {
            return [];
        }

        return array_keys($this->references[$referenceKey]);
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
        foreach ($this->references[$key][$criteria] as &$subset)
        {
            if ($multiple)
            {
                if (
                    isset($subset[$container])
                    && in_array($data, $subset[$container])
                )
                {
                    unset($subset);
                    continue;
                }

                $subset[$container][] = &$data;
                unset($subset);

                continue;
            }

            if (isset($subset[$container]))
            {
                $subset[$container] = $data;
            }
            else
            {
                $subset[$container] = &$data;
            }

            unset($subset);
        }
    }

    /**
     * Internal method to mount valid table alias to all where conditions. Will replace all {table}
     * occurrences with real table alias.
     *
     * @param array  $where
     * @param string $tableAlias
     * @return array
     */
    protected function castWhere(array $where, $tableAlias)
    {
        $result = [];

        foreach ($where as $column => $value)
        {
            if (is_string($column) && !is_int($column))
            {
                $column = str_replace('{table}', $tableAlias, $column);
            }

            if (is_array($value))
            {
                $value = $this->castWhere($value, $tableAlias);
            }

            $result[$column] = $value;
        }

        return $result;
    }

    /**
     * Clean loader data.
     *
     * @param bool $reconfigure Use this flag to reset configured flag to force query clarification
     *                          on next query creation.
     */
    public function clean($reconfigure = false)
    {
        $this->duplicates = [];
        $this->references = [];
        $this->references = [];
        $this->result = [];

        if ($reconfigure)
        {
            $this->configured = false;
        }

        foreach ($this->loaders as $loader)
        {
            //POSTLOAD created unique Selector every time, meaning we will have to flush flag
            //indicates that associated selector was configured
            $loader->clean($reconfigure || $this->options['method'] == Selector::POSTLOAD);
        }
    }

    /**
     * Destruct loader.
     */
    public function __destruct()
    {
        $this->clean();
    }
}