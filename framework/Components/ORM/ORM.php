<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\ORM\Exporters\DocumentationExporter;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Facades\Log;

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
    protected $core = null;

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
     * ORM component instance.
     *
     * @param CoreInterface $core
     * @param Container     $container
     */
    public function __construct(CoreInterface $core, Container $container)
    {
        $this->core = $core;
        $this->container = $container;

        $this->config = $core->loadConfig('orm');
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
            $this->schema = $this->core->loadData('ormSchema');
        }

        if (!isset($this->schema[$item]) && $update)
        {
            $this->updateSchema();
        }

        return $this->schema[$item];
    }


    public function getRelation(
        ActiveRecord $parent = null,
        $type,
        array $definition,
        $data = array()
    )
    {
        $class = $this->config['relations'][$type]['class'];

        return new $class($this, $parent, $definition, $data);
    }

    /**
     * Get ORM schema reader. Schema will detect all declared entities, their tables, columns,
     * relationships and etc.
     *
     * @return SchemaBuilder
     */
    public function schemaBuilder()
    {
        return SchemaBuilder::make(array(
            'config' => $this->config
        ), $this->container);
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
        $this->core->saveData('ormSchema', $this->schema);

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
}