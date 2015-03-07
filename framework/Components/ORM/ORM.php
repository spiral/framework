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

class ORM extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'orm';

    /**
     * WORK IN FUCKING PROGRESS :) BUT DBAL IS READY SO NOTHING HARD IS EXPECTED
     */

    /**
     * flow:
     * 1) index all models, exclude abstract ones
     * 2) collect declared columns and indexes
     * 3) collect all declared relationships (do we need to support new relationships?)
     * 4) send classes to SchemaReflector to build necessary table schemas (including map tables and foreign keys)
     *      4.1) find way to clearly resolve map tables and their columns?
     * 5) reflect schema to database
     * 6) normalize and export schema, build virtual documentation (this is the same thing as before)
     *
     *
     * Challenges:
     * 1) do i need new relationship types?
     * 2) load() with parents and child? any other relationships
     * 3) with() method is queries?
     * 4) managing map tables as before or new way?
     * 5-low) chunk based interaction, has to be done ON DBAL\SelectQuery level
     *
     */
}