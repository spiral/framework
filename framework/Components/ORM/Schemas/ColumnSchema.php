<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

class ColumnSchema
{
    protected $name = '';
    protected $type = '';
    protected $size = '';

    /**
     * New ColumnSchema instance. ColumnSchema build based on short string description given to column, this is NOT same
     * schema as DBAL\SchemaSchema but more like column project.
     *
     * @param string $definition Column definition in plain string form.
     * @param mixed  $default    Requested default value.
     */
    public function __construct($definition, $default = null)
    {

    }
}