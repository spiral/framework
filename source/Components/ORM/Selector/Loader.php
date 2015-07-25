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
     * Related model schema.
     *
     * @invisible
     * @var array
     */
    protected $schema = [];

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
     * Loader options usually used while declaring joins and etc.
     *
     * @var array
     */
    protected $options = [
        'method' => null,
        'alias' => null,
        'using' => null,
        'where' => null
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
     * Loaders used purely for conditional purposes.
     *
     * @var Loader[]
     */
    protected $joiners = [];

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

        //Related model schema
        $this->schema = $orm->getSchema($definition[static::RELATION_TYPE]);

        $this->container = $container;
        $this->definition = $definition;
        $this->parent = $parent;

        //Compiling options
        $this->options['method'] = static::LOAD_METHOD;

        if (!empty($parent) && $parent->getDatabase() != $this->getDatabase())
        {
            //We have to force post-load if parent loader database is different
            $this->options['method'] = Selector::POSTLOAD;
        }

        $this->columns = array_keys($this->schema[ORM::E_COLUMNS]);
    }

    /**
     * Is loader represent multiple records or one.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return static::MULTIPLE;
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
     * Instance of Dbal\Database data should be loaded from.
     *
     * @return Database
     */
    public function dbalDatabase()
    {
        return $this->orm->getDatabase($this->schema[ORM::E_DB]);
    }

    /**
     * Table alias to be used in query.
     *
     * @return string
     */
    public function getAlias()
    {
        if (!empty($this->options['using']))
        {
            //We are using another relation (presumably defined by with() to load data).
            return $this->options['using'];
        }

        if (!empty($this->options['alias']))
        {
            return $this->options['alias'];
        }

        if (empty($this->parent))
        {
            $alias = $this->getTable();
        }
        elseif ($this->parent instanceof Selector\Loaders\RootLoader)
        {
            $alias = $this->container;
        }
        else
        {
            $alias = $this->parent->getAlias() . '_' . $this->container;
        }

        if ($this->options['method'] == Selector::INLOAD && !empty($this->parent))
        {
            //We have to prefix all INLOADs to prevent collision with joiners
            $alias .= '_data';
        }

        return $alias;
    }

    /**
     * Get primary key name related to loader model.
     *
     * @return string|null
     */
    public function getPrimaryKey()
    {
        if (!isset($this->schema[ORM::E_PRIMARY_KEY]))
        {
            return null;
        }

        return $this->getAlias() . '.' . $this->schema[ORM::E_PRIMARY_KEY];
    }

    /**
     * Get aliased key of requested type.
     *
     * @param string $key
     * @return string|null
     */
    protected function getKey($key)
    {
        if (!isset($this->definition[$key]))
        {
            return null;
        }

        return $this->getAlias() . '.' . $this->definition[$key];
    }

    /**
     * Receive aliases key pointing to parent criteria (OUTER KEY).
     *
     * @return string
     */
    protected function getParentKey()
    {
        return $this->parent->getAlias() . '.' . $this->definition[ActiveRecord::INNER_KEY];
    }

    /**
     * Indicates that loader columns should be included into query statement.
     *
     * @return bool
     */
    public function isLoaded()
    {
        if (!empty($this->parent) && !$this->parent->isLoaded())
        {
            return false;
        }

        return $this->options['method'] !== Selector::JOIN;
    }

    /**
     * Indicated that related table was join and has to be parsed regular way.
     *
     * @return bool
     */
    protected function isJoined()
    {
        if (!empty($this->options['using']))
        {
            return true;
        }

        return in_array($this->options['method'], [Selector::INLOAD, Selector::JOIN]);
    }

    /**
     * Join type depends on how we going to use joined data.
     *
     * @return string
     */
    protected function joinType()
    {
        return $this->options['method'] == Selector::JOIN ? 'INNER' : 'LEFT';
    }

    /**
     * Update loader options.
     *
     * @param array $options
     * @return $this
     * @throws ORMException
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options + $this->options;

        if (
            $this->isJoined()
            && !empty($this->parent)
            && $this->parent->getDatabase() != $this->getDatabase()
        )
        {
            throw new ORMException(
                "Unable to use join tables located in different databases."
            );
        }

        return $this;
    }

    /**
     * Pre-load data on inner relation or relation chain.
     *
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return LoaderInterface
     */
    public function loader($relation, array $options = [])
    {
        if (($position = strpos($relation, '.')) !== false)
        {
            $parentRelation = substr($relation, 0, $position);

            //Recursively (will work only with ORM loaders).
            return $this->loader($parentRelation, [])->loader(
                substr($relation, $position + 1),
                $options
            );
        }

        if (!isset($this->schema[ORM::E_RELATIONS][$relation]))
        {
            $container = $this->container ?: $this->schema[ORM::E_ROLE_NAME];

            throw new ORMException("Undefined relation '{$relation}' under '{$container}'.");
        }

        if (!empty($chainMethod))
        {
            $options['method'] = $chainMethod;
        }

        if (isset($this->loaders[$relation]))
        {
            //Updating existed loaded options
            $this->loaders[$relation]->setOptions($options);

            return $this->loaders[$relation];
        }

        $relationOptions = $this->schema[ORM::E_RELATIONS][$relation];

        $loader = $this->orm->relationLoader(
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
     * Create sub-loader to be used as query filter.
     *
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return Loader
     */
    public function joiner($relation, array $options = [])
    {
        //We have to force joining method for full chain
        $options['method'] = Selector::JOIN;

        if (($position = strpos($relation, '.')) !== false)
        {
            $parentRelation = substr($relation, 0, $position);

            //Recursively (will work only with ORM loaders).
            return $this->joiner($parentRelation, [])->joiner(
                substr($relation, $position + 1),
                $options
            );
        }

        if (!isset($this->schema[ORM::E_RELATIONS][$relation]))
        {
            $container = $this->container ?: $this->schema[ORM::E_ROLE_NAME];
            throw new ORMException("Undefined relation '{$relation}' under '{$container}'.");
        }

        if (isset($this->joiners[$relation]))
        {
            //Updating existed joiner options
            return $this->joiners[$relation]->setOptions($options);
        }

        $relationOptions = $this->schema[ORM::E_RELATIONS][$relation];

        $joiner = $this->orm->relationLoader(
            $relationOptions[ORM::R_TYPE],
            $relation,
            $relationOptions[ORM::R_DEFINITION],
            $this
        );

        return $this->joiners[$relation] = $joiner->setOptions($options);
    }

    /**
     * Create selector to be executed as post load, usually such selector use aggregated values
     * and IN where syntax.
     *
     * @return Selector|null
     */
    public function createSelector()
    {
        if (!$this->isLoaded())
        {
            return null;
        }

        $selector = new Selector($this->definition[static::RELATION_TYPE], $this->orm, $this);
        $this->configureColumns($selector);

        foreach ($this->loaders as $loader)
        {
            $loader->configureSelector($selector);
        }

        foreach ($this->joiners as $joiner)
        {
            $joiner->configureSelector($selector);
        }

        return $selector;
    }

    /**
     * Clarify parent selection conditions.
     *
     * @param bool $loaders Configure sub loaders.
     * @param bool $joiners Configure joiners.
     * @param Selector $selector
     */
    public function configureSelector(Selector $selector, $loaders = true, $joiners = true)
    {
        if (!$this->isJoined())
        {
            /**
             * Sometimes loaded can be used as data source, in this case we have to allow
             * sub loaded to load data.
             */
            if (empty($this->parent))
            {
                foreach ($this->loaders as $joiners)
                {
                    $joiners->configureSelector($selector);
                }

                foreach ($this->joiners as $joiner)
                {
                    $joiner->configureSelector($selector);
                }
            }

            return;
        }

        if (!$this->configured)
        {
            $this->configureColumns($selector);

            //Inload conditions and etc
            if (empty($this->options['using']) && !empty($this->parent))
            {
                $this->clarifySelector($selector);
            }

            $this->configured = true;
        }

        foreach ($this->loaders as $joiners)
        {
            $joiners->configureSelector($selector);
        }

        foreach ($this->joiners as $joiner)
        {
            $joiner->configureSelector($selector);
        }
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
    public function postload()
    {
        foreach ($this->loaders as $loader)
        {
            if ($loader instanceof Loader && !$loader->isJoined())
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
     * @return bool
     */
    public function parseRow(array $row)
    {
        if (!$this->isLoaded())
        {
            return;
        }

        //Fetching only required part of resulted row
        $data = $this->fetchData($row);

        if (empty($this->parent))
        {
            if ($this->deduplicate($data))
            {
                //Yes, this is reference, i'm using this method to build data tree using nested parsers
                $this->result[] = &$data;
                $this->collectReferences($data);
            }

            $this->parseNested($row);

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
     * using INLOAD/JOIN_ONLY method).
     *
     * @param array $row
     */
    protected function parseNested(array $row)
    {
        foreach ($this->loaders as $loader)
        {
            if ($loader instanceof Loader && $loader->isJoined() && $loader->isLoaded())
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

        //Let's force data containers
        foreach ($this->loaders as $container => $loader)
        {
            $data[$container] = $loader->isMultiple() ? [] : null;
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

        return array_unique(array_keys($this->references[$referenceKey]));
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
    public function mount($container, $key, $criteria, array &$data, $multiple = false)
    {
        foreach ($this->references[$key][$criteria] as &$subset)
        {
            if ($multiple)
            {
                if (isset($subset[$container]) && in_array($data, $subset[$container]))
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
                $data = &$subset[$container];
            }
            else
            {
                $subset[$container] = &$data;
            }

            unset($subset);
        }
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
        $this->result = [];

        if ($reconfigure)
        {
            $this->configured = false;
        }

        foreach ($this->loaders as $loader)
        {
            //POSTLOAD created unique Selector every time, meaning we will have to flush flag
            //indicates that associated selector was configured
            $loader->clean($reconfigure || !$this->isJoined());
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