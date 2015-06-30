<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Sqlite;

use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;

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
            'type'       => 'integer',
            'primaryKey' => true,
            'nullable'   => false
        ],
        'bigPrimary'  => [
            'type'       => 'integer',
            'primaryKey' => true,
            'nullable'   => false
        ],

        //Enum type (mapped via method)
        'enum'        => 'enum',

        //Logical types
        'boolean'     => 'boolean',

        //Integer types (size can always be changed with size method), longInteger has method alias
        //bigInteger
        'integer'     => 'integer',
        'tinyInteger' => 'tinyint',
        'bigInteger'  => 'bigint',

        //String with specified length (mapped via method)
        'string'      => 'text',

        //Generic types
        'text'        => 'text',
        'tinyText'    => 'text',
        'longText'    => 'text',

        //Real types
        'double'      => 'double',
        'float'       => 'real',

        //Decimal type (mapped via method)
        'decimal'     => 'numeric',

        //Date and Time types
        'datetime'    => 'datetime',
        'date'        => 'date',
        'time'        => 'time',
        'timestamp'   => 'timestamp',

        //Binary types
        'binary'      => 'blob',
        'tinyBinary'  => 'blob',
        'longBinary'  => 'blob',

        //Additional types
        'json'        => 'text'
    ];

    /**
     * Driver specific reverse mapping, this mapping should link database type to one of standard
     * internal types. Not resolved types will be marked as "unknown" which will map them as php type
     * string.
     *
     * Attention, this mapping is valid only for tables created by spiral.
     *
     * @invisible
     * @var array
     */
    protected $reverseMapping = [
        'primary'     => [['type' => 'integer', 'primaryKey' => true]],
        'enum'        => ['enum'],
        'boolean'     => ['boolean'],
        'integer'     => ['int', 'integer', 'smallint', 'mediumint'],
        'tinyInteger' => ['tinyint'],
        'bigInteger'  => ['bigint'],
        'text'        => ['text', 'string'],
        'double'      => ['double'],
        'float'       => ['real'],
        'decimal'     => ['numeric'],
        'datetime'    => ['datetime'],
        'date'        => ['date'],
        'time'        => ['time'],
        'timestamp'   => ['timestamp'],
        'binary'      => ['blob']
    ];

    /**
     * Is column primary key.
     *
     * @var bool
     */
    protected $primaryKey = false;

    /**
     * Parse schema information provided by parent TableSchema and populate column values.
     *
     * @param mixed $schema Column information fetched from database by TableSchema. Format depends
     *                      on database type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->name = $schema['name'];
        $this->nullable = !$schema['notnull'];
        $this->type = $schema['type'];
        $this->primaryKey = (bool)$schema['pk'];

        $this->defaultValue = $schema['dflt_value'];

        if (preg_match('/^[\'""].*?[\'"]$/', $this->defaultValue))
        {
            $this->defaultValue = substr($this->defaultValue, 1, -1);
        }

        if (!preg_match('/^(?P<type>[a-z]+) *(?:\((?P<options>[^\)]+)\))?/', $this->type, $matches))
        {
            return;
        }

        $this->type = $matches['type'];

        $options = null;
        if (!empty($matches['options']))
        {
            $options = $matches['options'];
        }

        if ($this->type == 'enum')
        {
            $name = $this->getName(true);
            foreach ($schema['tableStatement'] as $column)
            {
                if (preg_match(
                    "/$name +enum.*?CHECK *\\($name in \\((.*?)\\)\\)/i",
                    trim($column),
                    $matches
                ))
                {
                    $enumValues = explode(',', $matches[1]);
                    foreach ($enumValues as &$value)
                    {
                        if (preg_match("/^'?(.*?)'?$/", trim($value), $matches))
                        {
                            //In database: 'value'
                            $value = $matches[1];
                        }

                        unset($value);
                    }

                    $this->enumValues = $enumValues;
                }
            }
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
    }

    /**
     * Get database specific enum type definition. Should not include database type and column name.
     *
     * @return string.
     */
    protected function enumType()
    {
        return '';
    }

    /**
     * Compile column create statement.
     *
     * @return string
     */
    public function sqlStatement()
    {
        $statement = parent::sqlStatement();
        if ($this->abstractType() != 'enum')
        {
            return $statement;
        }

        $enumValues = [];
        foreach ($this->enumValues as $value)
        {
            $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
        }

        return "$statement CHECK ({$this->getName(true)} IN (" . join(', ', $enumValues) . "))";
    }
}