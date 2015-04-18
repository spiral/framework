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
     */
    public function updateSchema()
    {
        $schema = $this->schemaBuilder();

        if (!empty($this->config['documentation']))
        {
            //Virtual ORM documentation to help IDE
            //            DocumentationExporter::make(compact('schema'))->render(
            //                $this->config['documentation']
            //            );
        }

        //Building database!
        $schema->executeSchema();

        $this->schema = $this->event('schema', $schema->normalizeSchema());

        //Saving
        $this->core->saveData('ormSchema', $this->schema);
    }


    /**
     * flow:
     * 1) index all models, exclude abstract ones - CHECK
     * 2) collect declared columns and indexes - CHECK
     * 3) collect all declared relationships (do we need to support new relationships?) - DO NOW
     * 4) send classes to SchemaReflector to build necessary table schemas (including map tables and foreign keys)
     *      4.1) find way to clearly resolve map tables and their columns?
     * 5) reflect schema to database - PARTIALLY CHECK
     * 6) normalize and export schema, build virtual documentation (this is the same thing as before)
     *
     * Challenges:
     * 1) do i need new relationship types? - ONLY FEW
     * 2) load() with parents and child? any other relationships
     * 3) with() method is queries?
     * 4) managing map tables as before or new way?
     * 5-low) chunk based interaction, has to be done ON DBAL\SelectQuery level
     */

    //force validations?

    /**
     * Normalized entity constants.
     */
    const E_TABLE    = 0;
    const E_DB       = 1;
    const E_DEFAULTS = 2;
}