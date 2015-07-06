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
    protected $mapping = [
        //Primary sequences
        'primary'     => [
            'type'          => 'int',
            'size'          => 11,
            'autoIncrement' => true,
            'nullable'      => false
        ],
        'bigPrimary'  => [
            'type'          => 'bigint',
            'size'          => 20,
            'autoIncrement' => true,
            'nullable'      => false
        ],

        //Enum type (mapped via method)
        'enum'        => 'enum',

        //Logical types
        'boolean'     => ['type' => 'tinyint', 'size' => 1],

        //Integer types (size can always be changed with size method), longInteger has method alias
        //bigInteger
        'integer'     => ['type' => 'int', 'size' => 11],
        'tinyInteger' => ['type' => 'tinyint', 'size' => 4],
        'bigInteger'  => ['type' => 'bigint', 'size' => 20],

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
        'timestamp'   => [
            'type'         => 'timestamp',
            'defaultValue' => MySqlDriver::DEFAULT_DATETIME
        ],

        //Binary types
        'binary'      => 'blob',
        'tinyBinary'  => 'tinyblob',
        'longBinary'  => 'longblob',

        //Additional types
        'json'        => 'text'
    ];

    /**
     * Driver specific reverse mapping, this mapping should link database type to one of standard
     * internal types. Not resolved types will be marked as "unknown" which will map them as php type
     * string.
     *
     * @invisible
     * @var array
     */
    protected $reverseMapping = [
        'primary'     => [['type' => 'int', 'autoIncrement' => true]],
        'bigPrimary'  => ['serial', ['type' => 'bigint', 'autoIncrement' => true]],
        'enum'        => ['enum'],
        'boolean'     => ['bool', 'boolean', ['type' => 'tinyint', 'size' => 1]],
        'integer'     => ['int', 'integer', 'smallint', 'mediumint'],
        'tinyInteger' => ['tinyint'],
        'bigInteger'  => ['bigint'],
        'string'      => ['varchar', 'char'],
        'text'        => ['text', 'mediumtext'],
        'tinyText'    => ['tinytext'],
        'longText'    => ['longtext'],
        'double'      => ['double'],
        'float'       => ['float', 'real'],
        'decimal'     => ['decimal'],
        'datetime'    => ['datetime'],
        'date'        => ['date'],
        'time'        => ['time'],
        'timestamp'   => ['timestamp'],
        'binary'      => ['blob', 'binary', 'varbinary'],
        'tinyBinary'  => ['tinyblob'],
        'longBinary'  => ['longblob']
    ];

    /**
     * List of types forbids default value set.
     *
     * @var array
     */
    protected $forbiddenDefaults = [
        'text', 'mediumtext', 'tinytext', 'longtext', 'blog', 'tinyblob', 'longblob'
    ];

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
     * Get column default value, value will be automatically converted to appropriate internal type.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        $defaultValue = parent::getDefaultValue();

        if (in_array($this->type, $this->forbiddenDefaults))
        {
            return null;
        }

        return $defaultValue;
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
            return Timestamp::castTimestamp($this->defaultValue, 'UTC');
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
        $defaultValue = $this->defaultValue;
        if (in_array($this->type, $this->forbiddenDefaults))
        {
            //Flushing default value for forbidden types
            $this->defaultValue = null;

            self::logger()->warning("Default value is not allowed for MySQL type '{type}'.", [
                'type' => $this->type
            ]);
        }

        $statement = parent::sqlStatement();

        $this->defaultValue = $defaultValue;

        if ($this->autoIncrement)
        {
            return "{$statement} AUTO_INCREMENT";
        }

        return $statement;
    }
}