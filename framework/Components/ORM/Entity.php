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

class Entity extends Component
{
    /**
     * ORM requested schema analysis. This constant will be send as option while analysis.
     */
    const SCHEMA_ANALYSIS = 788;

    /**
     * Model specific constant to indicate that model has to be validated while saving. You still can
     * change this behaviour manually by providing argument to save method.
     */
    const FORCE_VALIDATION = true;

    /**
     * Set this constant to false to disable automatic column, index and foreign keys creation.
     * By default entities will read schema from database, so you can connect your ORM model to
     * already existed table.
     */
    const ACTIVE_SCHEMA = false;

    const HAS_ONE    = 1098;
    const HAS_MANY   = 2098;
    const BELONGS_TO = 3323;

    const MANY_TO_MANY = 4342;
    const MANY_THOUGHT = 5344;

    /**
     * MORPH CONNECTIONS.
     */
    const HAS_ONE_MORPHED    = 4342;
    const HAS_MANY_MORPHED   = 4342;
    const BELONGS_TO_MORPHED = 1212;

    const MANY_TO_MANY_MORPHED = 4342;
    const MORPHED_MANY_TO_MANY = 4342;

    /**
     * Key values.
     */
    const FOREIGN_KEY = 'foreign';
    const LOCAL_KEY   = 'local';
    const BACK_REF    = 'backref';

    const THOUGHT     = 'thought';
    const PIVOT_TABLE = 'thought';
    const VIA_TABLE   = 'thought';

    /**
     * Constants used to declare index type. See documentation for indexes property.
     */
    const INDEX  = 1000;
    const UNIQUE = 2000;
}