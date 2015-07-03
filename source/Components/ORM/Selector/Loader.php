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
     * List of keys has to be stored as data references. This set of keys is required by inner loader(s)
     * to quickly compile nested data.
     *
     * @var array
     */
    protected $referenceKeys = [];

    //----------------------------

    protected $references = [];

    protected $aggregatedReferences = [];

    protected $duplicates = [];

    protected $result = [];


    //-------------------------------

    public function __construct(
        ORM $orm,
        $container,
        array $definition = [],
        Loader $parent
    )
    {
        $this->orm = $orm;

        $this->container = $container;
        $this->definition = $definition;
        $this->parent = $parent;

        //Related model schema
        $this->schema = $orm->getSchema($this->getTarget());

        //Compiling options
        $this->options['method'] = static::LOAD_METHOD;

        if ($parent->dbalDatabase() != $this->dbalDatabase())
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
    public function loader($relation, array $options = [])
    {
        if (($position = strpos($relation, '.')) !== false)
        {
            $parentRelation = substr($relation, 0, $position);

            //Recursively
            return $this->loader($parentRelation)->loader(
                substr($relation, $position + 1),
                $options
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
            $this->loaders[$relation]->setOptions($options);

            return $this->loaders[$relation];
        }

        $relationOptions = $this->schema[ORM::E_RELATIONS][$relation];

        $loader = $this->orm->getLoader(
            $relationOptions[ORM::R_TYPE],
            $relation,
            $relationOptions[ORM::R_DEFINITION],
            $this
        );

        $loader->setOptions($options);
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
     * @return Selector
     */
    public function createSelector()
    {
        $selector = new Selector($this->definition[static::RELATION_TYPE], $this->orm, $this);
        $this->columnsOffset = $selector->registerColumns($this, $this->columns);

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
            //Mounting columns
            if ($this->isLoadable())
            {
                $this->columnsOffset = $selector->registerColumns($this, $this->columns);
            }

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
     * ORM Loader specific method used to clarify selector conditions, join and columns with
     * loader specific information.
     *
     * @param Selector $selector
     */
    abstract protected function clarifySelector(Selector $selector);

    /**
     * Run post selection queries to clarify featched model data. Usually many conditions will be
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
    abstract public function parseRow(array $row);

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
        $row = array_slice($row, $this->columnsOffset, count($this->columns));

        //Populating keys
        return array_combine($this->columns, $row);
    }

    /**
     * Reference key (from parent object) required to speed up data normalization. In most of cases
     * this is primary key of parent model.
     *
     * @return string
     */
    public function getReferenceKey()
    {
        //Fairly simple logic
        return $this->definition[ActiveRecord::INNER_KEY];
    }

    //-------------------

    public function getReferenceName(array $data)
    {
        if (!isset($data[$this->definition[ActiveRecord::OUTER_KEY]]))
        {
            return null;
        }

        //Fairly simple logic
        return $this->definition[ActiveRecord::INNER_KEY] . '::' . $data[$this->definition[ActiveRecord::OUTER_KEY]];
    }


    public function getAggregatedKeys($key)
    {
        //TODO: DISABLE WHEN NOT REQUIRED

        if (!isset($this->aggregatedReferences[$key]))
        {
            return [];
        }

        return array_keys($this->aggregatedReferences[$key]);
    }

    public function registerNestedParent($container, $key, $value, &$data)
    {
        foreach ($this->aggregatedReferences[$key][$value] as &$subset)
        {
            if (!isset($subset[$container]))
            {
                $subset[$container] = &$data;
            }

            unset($subset);
        }
    }

    public function registerNested($referenceName, $container, array &$data, $multiple = false)
    {
        if (!isset($this->references[$referenceName]))
        {
            //Nothing to do
            return;
        }

        if ($multiple)
        {
            $this->references[$referenceName][$container][] = &$data;
        }
        else
        {
            if (!isset($this->references[$referenceName][$container]))
            {
                /**
                 * There is very tricky spot where you have to be careful (i spend 2 hours for debugging).
                 * If you will reassign references it will loose previous references and some sets of
                 * data will be broken.
                 */
                $this->references[$referenceName][$container] = &$data;
            }
        }
    }


    protected function checkDuplicate(array &$data)
    {
        if (isset($this->schema[ORM::E_PRIMARY_KEY]))
        {
            $primaryKey = $this->schema[ORM::E_PRIMARY_KEY];

            if (isset($this->duplicates[$data[$primaryKey]]))
            {
                //Duplicate is presented, let's reduplicate (will update reference)
                $data = $this->duplicates[$data[$primaryKey]];

                return true;
            }

            $this->duplicates[$data[$primaryKey]] = &$data;
        }
        else
        {
            /**
             * It is recommended to use primary keys in every model as it will speed up de-duplication.
             */
            $serialization = serialize($data);
            if (isset($this->duplicates[$serialization]))
            {
                //Duplicate is presented, let's reduplicate
                $data = $this->duplicates[$serialization];

                //Duplicate is presented
                return true;
            }

            $this->duplicates[$serialization] = &$data;
        }

        return false;
    }

    protected function registerReferences(array &$data)
    {
        foreach ($this->referenceKeys as $key)
        {
            //Adding reference
            $this->references[$key . '::' . $data[$key]] = &$data;
            $this->aggregatedReferences[$key][$data[$key]][] = &$data;
        }
    }


    //-------------------------------------

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
        $this->aggregatedReferences = [];
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