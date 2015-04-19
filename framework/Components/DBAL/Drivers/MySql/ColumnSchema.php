<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;
use Spiral\Components\DBAL\SqlFragment;
use Spiral\Support\Models\Accessors\Timestamp;

class ColumnSchema extends AbstractColumnSchema
{
    /**
     * Direct mapping from base abstract type to database internal type with specified data options,
     * such as size, precision scale, unsigned flag and etc. Every declared type can be assigned using
     * ->type() method, however to pass custom type parameters, methods has to be declared in database
     * specific ColumnSchema. Type identifier not necessary should be real type name.
     *
     * Example:
     * integer => array('type' => 'int', 'size' => 1),
     * boolean => array('type' => 'tinyint', 'size' => 1)
     *
     * @invisible
     * @var array
     */
    protected $mapping = array(
        //Primary sequences
        'primary'     => array('type' => 'int', 'size' => 11, 'autoIncrement' => true),
        'bigPrimary'  => array('type' => 'bigint', 'size' => 20, 'autoIncrement' => true),

        //Enum type (mapped via method)
        'enum'        => 'enum',

        //Logical types
        'boolean'     => array('type' => 'tinyint', 'size' => 1),

        //Integer types (size can always be changed with size method), longInteger has method alias
        //bigInteger
        'integer'     => array('type' => 'int', 'size' => 11),
        'tinyInteger' => array('type' => 'tinyint', 'size' => 4),
        'bigInteger'  => array('type' => 'bigint', 'size' => 20),

        //String with specified length (mapped via method)
        'string'      => 'varchar',

        //Generic types
        'text'        => 'text',
        'tinyText'    => 'tinytext',
        'longText'    => 'longtext',

        //Real types
        'double'      => 'double',
        'float'       => 'float',

        //Decimal type (mapped via method)
        'decimal'     => 'decimal',

        //Date and Time types
        'datetime'    => 'datetime',
        'date'        => 'date',
        'time'        => 'time',
        'timestamp'   => array(
            'type'         => 'timestamp',
            'defaultValue' => MySqlDriver::DEFAULT_DATETIME
        ),

        //Binary types
        'binary'      => 'blob',
        'tinyBinary'  => 'tinyblob',
        'longBinary'  => 'longblob',

        //Additional types
        'json'        => 'text'
    );

    /**
     * Driver specific reverse mapping, this mapping should link database type to one of standard
     * internal types. Not resolved types will be marked as "unknown" which will map them as php type
     * string.
     *
     * @invisible
     * @var array
     */
    protected $reverseMapping = array(
        'primary'     => array(array('type' => 'int', 'autoIncrement' => true)),
        'bigPrimary'  => array('serial', array('type' => 'bigint', 'autoIncrement' => true)),
        'enum'        => array('enum'),
        'boolean'     => array('bool', 'boolean', array('type' => 'tinyint', 'size' => 1)),
        'integer'     => array('int', 'integer', 'smallint', 'mediumint'),
        'tinyInteger' => array('tinyint'),
        'bigInteger'  => array('bigint'),
        'string'      => array('varchar', 'char'),
        'text'        => array('text', 'mediumtext'),
        'tinyText'    => array('tinytext'),
        'longText'    => array('longtext'),
        'double'      => array('double'),
        'float'       => array('float', 'real'),
        'decimal'     => array('decimal'),
        'datetime'    => array('datetime'),
        'date'        => array('date'),
        'time'        => array('time'),
        'timestamp'   => array('timestamp'),
        'binary'      => array('blob', 'binary', 'varbinary'),
        'tinyBinary'  => array('tinyblob'),
        'longBinary'  => array('longblob')
    );

    /**
     * Field is auto incremental.
     *
     * @var bool
     */
    protected $autoIncrement = false;

    /**
     * Parse column information provided by parent TableSchema and populate column values.
     *
     * @param mixed $schema Column information fetched from database by TableSchema. Format depends
     *                      on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->type = $schema['Type'];
        $this->nullable = strtolower($schema['Null']) == 'yes';
        $this->defaultValue = $schema['Default'];
        $this->autoIncrement = stripos($schema['Extra'], 'auto_increment') !== false;

        if (!preg_match('/^(?P<type>[a-z]+)(?:\((?P<options>[^\)]+)\))?/', $this->type, $matches))
        {
            return;
        }

        $this->type = $matches['type'];

        $options = null;
        if (!empty($matches['options']))
        {
            $options = $matches['options'];
        }

        if ($this->abstractType() == 'enum')
        {
            $this->enumValues = array_map(function ($value)
            {
                return trim($value, $value[0]);
            }, explode(',', $options));

            return;
        }

        $options = array_map(function ($value)
        {
            return intval($value);
        }, explode(',', $options));

        if (count($options) > 1)
        {
            list($this->precision, $this->scale) = $options;
        }
        elseif ($options)
        {
            $this->size = $options[0];
        }

        //Default value conversions
        if ($this->type == 'bit' && $this->defaultValue)
        {
            //Cutting b\ and '
            $this->defaultValue = new SqlFragment($this->defaultValue);
        }

        if ($this->abstractType() == 'timestamp' && $this->defaultValue == '0000-00-00 00:00:00')
        {
            $this->defaultValue = MySqlDriver::DEFAULT_DATETIME;
        }
    }

    /**
     * Prepare default value to be used in sql statements, string values will be quoted.
     *
     * @return string
     */
    protected function prepareDefault()
    {
        if ($this->abstractType() == 'timestamp' && is_scalar($this->defaultValue))
        {
            return Timestamp::castTimestamp($this->defaultValue);
        }

        return parent::prepareDefault();
    }

    /**
     * Compile column create statement.
     *
     * @return string
     */
    public function sqlStatement()
    {
        $statement = parent::sqlStatement();
        if ($this->autoIncrement)
        {
            return "{$statement} AUTO_INCREMENT";
        }

        return $statement;
    }
}