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
use Spiral\Core\Component;

class Selector extends Component
{
    /**
     * Schema of related model.
     *
     * @var array
     */
    protected $schema = array();

    protected $database = null;

    /**
     * ORM component. Used to access related schemas.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    protected $loadingSchema = array();

    public function __construct(array $schema, Database $database, ORM $orm, array $query = array())
    {
        $this->schema = $schema;
        $this->database = $database;

        $this->orm = $orm;
    }
}