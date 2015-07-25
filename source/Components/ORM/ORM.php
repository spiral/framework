<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\ORM\Exporters\DocumentationExporter;
use Spiral\Components\ORM\Schemas\ModelSchema;
use Spiral\Components\ORM\Schemas\RelationSchemaInterface;
use Spiral\Components\ORM\Selector\LoaderInterface;
use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\RuntimeCacheInterface;

class ORM extends Component
{
    /**
     * Required traits.
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Core component.
     *
     * @var CoreInterface
     */
    protected $runtime = null;

    /**
     * DatabaseManager.
     *
     * @var DatabaseManager
     */
    protected $dbal = null;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Loaded entities schema. Schema contains full description about model behaviours, relations,
     * columns and etc.
     *
     * @var array|null
     */
    protected $schema = null;

    /**
     * In cases when ORM cache is enabled every constructed instance will be stored here, cache used
     * mainly to ensure the same instance of object, even if it's accessed from different spots.
     *
     * Cache will increase memory consumption.
     *
     * @var ActiveRecord[]
     */
    protected $entityCache = [];

    /**
     * ORM component instance.
     *
     * @param ConfiguratorInterface $configurator
     * @param RuntimeCacheInterface $runtime
     * @param DatabaseManager       $dbal
     * @param Container             $container
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        RuntimeCacheInterface $runtime,
        DatabaseManager $dbal,
        Container $container
    )
    {
        $this->runtime = $runtime;
        $this->dbal = $dbal;
        $this->container = $container;

        $this->config = $configurator->getConfig('orm');
    }

    /**
     * Enable or disable entity cache.
     *
     * @param bool $enabled
     * @param int  $maxSize
     * @return static
     */
    public function entityCache($enabled, $maxSize = null)
    {
        $this->config['entityCache']['enabled'] = (bool)$enabled;
        if (!empty($maxSize))
        {
            $this->config['entityCache']['maxSize'] = $maxSize;
        }

        return $this;
    }

    /**
     * Flush content of entity cache.
     */
    public function flushCache()
    {
        $this->entityCache = [];
    }

    /**
     * Change associated DatabaseManager.
     *
     * @param DatabaseManager $dbal
     */
    public function setDBAL(DatabaseManager $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Most of classes with ORM access will need DatabaseManager as well.
     *
     * @return DatabaseManager
     */
    public function getDBAL()
    {
        return $this->dbal;
    }

    /**
     * Get database by it's name from DBAL associated with ORM component.
     *
     * @param string $database
     * @return Database
     */
    public function getDatabase($database)
    {
        return $this->dbal->db($database);
    }

    /**
     * Get associated Container, can be used inside custom relations and loaders to load additional
     * components.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get schema for specified document class or collection.
     *
     * @param string $item   Document class or collection name (including database).
     * @param bool   $update Automatically update schema if requested schema is missing.
     * @return mixed
     */
    public function getSchema($item, $update = true)
    {
        if ($this->schema === null)
        {
            $this->schema = $this->runtime->loadData('ormSchema');
        }

        if (!isset($this->schema[$item]) && $update)
        {
            $this->updateSchema();
        }

        return $this->schema[$item];
    }

    /**
     * Get ORM schema reader. Schema will detect all declared entities, their tables, columns,
     * relationships and etc.
     *
     * @return SchemaBuilder
     */
    public function schemaBuilder()
    {
        return SchemaBuilder::make([
            'config' => $this->config,
            'orm' => $this
        ], $this->container);
    }

    /**
     * Instance of ActiveRecord relation accessor.
     *
     * @param int          $type
     * @param ActiveRecord $parent
     * @param array        $definition
     * @param array        $data
     * @param bool         $loaded
     * @return mixed
     */
    public function relation($type, ActiveRecord $parent, $definition, $data = null, $loaded = false)
    {
        if (!isset($this->config['relations'][$type]['class']))
        {
            throw new ORMException("Undefined relation type '{$type}'.");
        }

        $class = $this->config['relations'][$type]['class'];

        return new $class($this, $parent, $definition, $data, $loaded);
    }

    /**
     * Instance of relation schema with specified type.
     *
     * @param mixed         $type
     * @param SchemaBuilder $schemaBuilder
     * @param ModelSchema   $model
     * @param string        $name
     * @param array         $definition
     * @return RelationSchemaInterface
     */
    public function relationSchema(
        $type,
        SchemaBuilder $schemaBuilder,
        ModelSchema $model,
        $name,
        array $definition
    )
    {
        if (!isset($this->config['relations'][$type]['schema']))
        {
            throw new ORMException("Undefined relation schema '{$type}'.");
        }

        $class = $this->config['relations'][$type]['schema'];

        return new $class($schemaBuilder, $model, $name, $definition);
    }

    /**
     * Get instance of Loader associated with relation type and relation definition.
     *
     * @param int             $type       Relation type.
     * @param string          $container  Container related to parent loader.
     * @param array           $definition Relation definition.
     * @param LoaderInterface $parent     Parent loader (if presented).
     * @return LoaderInterface
     * @throws ORMException
     */
    public function relationLoader($type, $container, array $definition, LoaderInterface $parent = null)
    {
        if (!isset($this->config['relations'][$type]['schema']))
        {
            throw new ORMException("Undefined relation loader '{$type}'.");
        }

        $class = $this->config['relations'][$type]['loader'];

        return new $class($this, $container, $definition, $parent);
    }

    /**
     * Construct instance of ActiveRecord or receive it from cache (if enabled).
     *
     * @param string $class
     * @param array  $data
     * @param bool   $cache
     * @return ActiveRecord
     */
    public function construct($class, array $data = [], $cache = true)
    {
        if (!$this->config['entityCache']['enabled'] || !$cache)
        {
            //Entity cache is disabled
            return new $class($data, !empty($data), $this);
        }

        //We have to find object criteria (will work for objects with primary key only)
        $criteria = null;
        if (
            !empty($this->schema[$class][ORM::E_PRIMARY_KEY])
            && !empty($data[$this->schema[$class][ORM::E_PRIMARY_KEY]])
        )
        {
            $criteria = $class . '.' . $data[$this->schema[$class][ORM::E_PRIMARY_KEY]];
        }

        if (isset($this->entityCache[$criteria]))
        {
            //Retrieving reconfigured model from the cache
            return $this->entityCache[$criteria]->setContext($data);
        }

        if (count($this->entityCache) > $this->config['entityCache']['maxSize'])
        {
            return new $class($data, !empty($data), $this);
        }

        return $this->entityCache[$criteria] = new $class($data, !empty($data), $this);
    }

    /**
     * Refresh ODM schema state, will reindex all found document models and render documentation for
     * them. This is slow method using Tokenizer, refreshSchema() should not be called by user request.
     *
     * @return SchemaBuilder
     */
    public function updateSchema()
    {
        $builder = $this->schemaBuilder();

        if (!empty($this->config['documentation']))
        {
            //Virtual ORM documentation to help IDE
            DocumentationExporter::make(compact('builder'), $this->container)->render(
                $this->config['documentation']
            );
        }

        //Building database!
        $builder->executeSchema();

        $this->schema = $this->event('schema', $builder->normalizeSchema());
        ActiveRecord::clearSchemaCache();

        //Saving
        $this->runtime->saveData('ormSchema', $this->schema);

        return $builder;
    }

    /**
     * Normalized entity constants.
     */
    const E_ROLE_NAME   = 0;
    const E_TABLE       = 1;
    const E_DB          = 2;
    const E_COLUMNS     = 3;
    const E_HIDDEN      = 4;
    const E_SECURED     = 5;
    const E_FILLABLE    = 6;
    const E_MUTATORS    = 7;
    const E_VALIDATES   = 8;
    const E_RELATIONS   = 9;
    const E_PRIMARY_KEY = 10;

    /**
     * Normalized relation options.
     */
    const R_TYPE       = 0;
    const R_TABLE      = 1;
    const R_DEFINITION = 2;

    /**
     * Pivot table location in ActiveRecord data.
     */
    const PIVOT_DATA = '@pivot';
}