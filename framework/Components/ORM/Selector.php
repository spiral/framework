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

class Selector extends Component
{
    /**
     * Schema of related model.
     *
     * @var array
     */
    protected $schema = array();

    /**
     * ORM component. Used to access related schemas.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    public function __construct(array $schema, ORM $orm, array $query = array())
    {
        $this->schema = $schema;
        $this->orm = $orm;
    }
}