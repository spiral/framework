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
use Spiral\Core\Core;

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
     * ORM component instance.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
        $this->config = $core->loadConfig('orm');
    }

    /**
     * Get ORM schema reader. Schema will detect all declared entities, their tables, columns,
     * relationships and etc.
     *
     * @return SchemaBuilder
     */
    public function schemaReader()
    {
        return SchemaBuilder::make(array(
            'config' => $this->config
        ));
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