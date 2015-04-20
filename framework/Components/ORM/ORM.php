<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Core\Component;
use Spiral\Core\CoreInterface;

class ORM extends Component
{
    /**
     * Required traits.
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'orm';

    /**
     * Core component.
     *
     * @var CoreInterface
     */
    protected $core = null;

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
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
        $this->config = $core->loadConfig('orm');
    }

    /**
     * Get schema for specified document class or collection.
     *
     * @param string $item Document class or collection name (including database).
     * @return mixed
     */
    public function getSchema($item)
    {
        if ($this->schema === null)
        {
            $this->schema = $this->core->loadData('ormSchema');
        }

        if (!isset($this->schema[$item]))
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
        return SchemaBuilder::make(array(
            'config' => $this->config
        ));
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
            //            DocumentationExporter::make(compact('builder'))->render(
            //                $this->config['documentation']
            //            );
        }

        //Building database!
        $builder->executeSchema();

        $this->schema = $this->event('schema', $builder->normalizeSchema());

        //Saving
        $this->core->saveData('ormSchema', $this->schema);

        return $builder;
    }

    /**
     * Normalized entity constants.
     */
    const E_TABLE       = 0;
    const E_DB          = 1;
    const E_DEFAULTS    = 2;
    const E_HIDDEN      = 3;
    const E_SECURED     = 4;
    const E_FILLABLE    = 5;
    const E_MUTATORS    = 6;
    const E_VALIDATES   = 7;
    const E_MESSAGES    = 8;
    const E_RELATIONS   = 9;
    const E_PRIMARY_KEY = 10;


    /**
     * Normalized relation options.
     */
    const R_TYPE       = 0;
    const R_DEFINITION = 1;
}