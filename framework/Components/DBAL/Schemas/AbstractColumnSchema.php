<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Schemas;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\SqlFragment;
use Spiral\Components\DBAL\SqlFragmentInterface;
use Spiral\Core\Component;

/**
 * @method static AbstractColumnSchema make(array $parameters = array());
 *
 * @method AbstractColumnSchema|$this boolean()
 *
 * @method AbstractColumnSchema|$this integer()
 * @method AbstractColumnSchema|$this tinyInteger()
 * @method AbstractColumnSchema|$this bigInteger()
 *
 * @method AbstractColumnSchema|$this text()
 * @method AbstractColumnSchema|$this tinyText()
 * @method AbstractColumnSchema|$this longText()
 *
 * @method AbstractColumnSchema|$this double()
 * @method AbstractColumnSchema|$this float()
 *
 * @method AbstractColumnSchema|$this datetime()
 * @method AbstractColumnSchema|$this date()
 * @method AbstractColumnSchema|$this time()
 * @method AbstractColumnSchema|$this timestamp()
 *
 * @method AbstractColumnSchema|$this binary()
 * @method AbstractColumnSchema|$this tinyBinary()
 * @method AbstractColumnSchema|$this longBinary()
 */
abstract class AbstractColumnSchema extends Component
{
    /**
     * Logging.
     */
    use Component\LoggerTrait;

    /**
     * Direct mapping from base abstract type to database internal type with specified data options,
     * such as size, precision scale, unsigned flag and etc. Every declared type can be assigned
     * using ->type() method, however to pass custom type parameters, methods has to be declared in
     * database specific ColumnSchema. Type identifier not necessary should be real type name.
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
        'primary'     => null,
        'bigPrimary'  => null,

        //Enum type (mapped via method)
        'enum'        => null,

        //Logical types
        'boolean'     => null,

        //Integer types (size can always be changed with size method), longInteger has method alias
        //bigInteger
        'integer'     => null,
        'tinyInteger' => null,
        'bigInteger'  => null,

        //String with specified length (mapped via method)
        'string'      => null,

        //Generic types
        'text'        => null,
        'tinyText'    => null,
        'longText'    => null,

        //Real types
        'double'      => null,
        'float'       => null,

        //Decimal type (mapped via method)
        'decimal'     => null,

        //Date and Time types
        'datetime'    => null,
        'date'        => null,
        'time'        => null,
        'timestamp'   => null,

        //Binary types
        'binary'      => null,
        'tinyBinary'  => null,
        'longBinary'  => null,

        //Additional types
        'json'        => null
    );

    /**
     * Abstract type aliases (for consistency).
     *
     * @var array
     */
    protected $aliases = array(
        'int'            => 'integer',
        'bigint'         => 'bigInteger',
        'incremental'    => 'primary',
        'bigIncremental' => 'bigPrimary',
        'bool'           => 'boolean'
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
        'primary'     => array(),
        'bigPrimary'  => array(),
        'enum'        => array(),
        'boolean'     => array(),
        'integer'     => array(),
        'tinyInteger' => array(),
        'bigInteger'  => array(),
        'string'      => array(),
        'text'        => array(),
        'tinyText'    => array(),
        'longText'    => array(),
        'double'      => array(),
        'float'       => array(),
        'decimal'     => array(),
        'datetime'    => array(),
        'date'        => array(),
        'time'        => array(),
        'timestamp'   => array(),
        'binary'      => array(),
        'tinyBinary'  => array(),
        'longBinary'  => array(),
        'json'        => array()
    );

    /**
     * Internal php mapping from abstract types to internal php type. Result of this conversion will
     * be used to process default values, declare attribute types and filters in ActiveRecords and etc.
     * String type is not listed there as string will be used as default type if no other matching
     * were found.
     *
     * @invisible
     * @var array
     */
    protected $phpMapping = array(
        'int'   => array('primary', 'bigPrimary', 'integer', 'tinyInteger', 'bigInteger'),
        'bool'  => array('boolean'),
        'float' => array('double', 'float', 'decimal')
    );

    /**
     * Column name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Parent table schema.
     *
     * @invisible
     * @var AbstractTableSchema
     */
    protected $table = null;

    /**
     * Database (driver) specific column type, this value will stay as it is until direct change by
     * one of type methods. In other words, there is no "forced" type mapping which allows to declare
     * custom columns without touching ones specified directly in table.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Defines if column value can be set to null. Attention, this flag is false by default, which
     * means that you can't alter tables with existed data without providing default value or setting
     * this flag to true.
     *
     * @var bool
     */
    protected $nullable = false;

    /**
     * Default column value, can not be applied to some datatypes (for example to primary keys), should
     * follow type size and other options.
     *
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * Column type size, can have different meanings for different datatypes.
     *
     * @var int
     */
    protected $size = 0;

    /**
     * Precision of column, applied only for "numeric" type.
     *
     * @var int
     */
    protected $precision = 0;

    /**
     * Scale of column, applied only for "numeric" type.
     *
     * @var int
     */
    protected $scale = 0;

    /**
     * Used only for enum types and declared possible enum values. In some DBMS enum type will be
     * emulated as column constrain.
     *
     * @var array
     */
    protected $enumValues = array();

    /**
     * ColumnSchema
     *
     * @param AbstractTableSchema $table  Parent TableSchema.
     * @param string              $name   Column name.
     * @param mixed               $schema Column information fetched from database by TableSchema.
     *                                    Format depends on database type.
     */
    public function __construct(AbstractTableSchema $table, $name, $schema = null)
    {
        $this->name = $name;
        $this->table = $table;

        $schema && $this->resolveSchema($schema);
    }

    /**
     * Parse column information provided by parent TableSchema and populate column values.
     *
     * @param mixed $schema Column information fetched from database by TableSchema. Format depends
     *                      on driver type.
     * @return mixed
     */
    abstract protected function resolveSchema($schema);

    /**
     * Column name.
     *
     * @param bool $quoted If true column name will be quoted accordingly to driver rules.
     * @return string
     */
    public function getName($quoted = false)
    {
        return $quoted ? $this->table->getDriver()->identifier($this->name) : $this->name;
    }

    /**
     * Give new name to column. Do not use this method to rename existed columns, use
     * TableSchema->renameColumn(). This is internal method used to rename column inside schema.
     *
     * @param string $name New column name.
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Internal database type, can vary based on database driver.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Column size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Column precision.
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Column scale value.
     *
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Associate one of abstract schema types with correct database type representation. Some types,
     * like primary, enum, string and etc will require it's own function with additional parameters.
     * Attention, type changing is not always possible and strongly depends on database type, even if
     * this is possible in some databases (MySQL, SQLite and PostgresSQL) try avoiding changing column
     * types without really strong reason.
     *
     * Attention, changing type of existed columns in some databases has a lot of restrictions like
     * cross type conversions and etc. Try do not change column type without a reason.
     *
     * @param string $type Abstract or virtual type declared in mapping.
     * @return static
     * @throws SchemaBuilderException
     */
    public function type($type)
    {
        if (isset($this->aliases[$type]))
        {
            $type = $this->aliases[$type];
        }

        if (!isset($this->mapping[$type]))
        {
            throw new SchemaBuilderException("Undefined abstract/virtual type '{$type}'.");
        }

        /**
         * Resetting all values to default state.
         */
        $this->size = $this->precision = $this->scale = 0;
        $this->enumValues = array();

        if (is_string($this->mapping[$type]))
        {
            $this->type = $this->mapping[$type];

            return $this;
        }

        foreach ($this->mapping[$type] as $property => $value)
        {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * Get abstract type name, this method will map one of database types to limited set of ColumnSchema
     * abstract types.
     *
     * Attention, this method is not used for schema comparasions (database type used), it's only for
     * decorative purposes. If schema can't resolve type - "unknown" will be returned (by default
     * mapped to php type string).
     *
     * @return string
     */
    public function abstractType()
    {
        foreach ($this->reverseMapping as $type => $candidates)
        {
            foreach ($candidates as $candidate)
            {
                if (is_string($candidate))
                {
                    if (strtolower($candidate) == strtolower($this->type))
                    {
                        return $type;
                    }

                    continue;
                }

                if (strtolower($candidate['type']) != strtolower($this->type))
                {
                    continue;
                }

                foreach ($candidate as $option => $required)
                {
                    if ($option == 'type')
                    {
                        continue;
                    }

                    if ($this->$option != $required)
                    {
                        continue 2;
                    }
                }

                return $type;
            }
        }

        return 'unknown';
    }

    /**
     * Get one of internal php types to represent column values (including default value):
     * integer (int),
     * boolean (bool),
     * string,
     * float.
     *
     * Mapping will be performed using phpMapping attribute values.
     *
     * @return string
     */
    public function phpType()
    {
        $schemaType = $this->abstractType();
        foreach ($this->phpMapping as $phpType => $candidates)
        {
            if (in_array($schemaType, $candidates))
            {
                return $phpType;
            }
        }

        return 'string';
    }

    /**
     * Can column store null value?
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Set column nullable.
     *
     * @param bool $nullable
     * @return static
     */
    public function nullable($nullable = true)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Get column default value, value will be automatically converted to appropriate internal type.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        if (is_null($this->defaultValue))
        {
            return null;
        }

        if ($this->defaultValue instanceof SqlFragmentInterface)
        {
            return $this->defaultValue;
        }

        if (in_array($this->abstractType(), array('time', 'date', 'datetime', 'timestamp')))
        {
            if (strtolower($this->defaultValue) == strtolower($this->table->getDriver()->timestampNow()))
            {
                return new SqlFragment($this->defaultValue);
            }
        }

        switch ($this->phpType())
        {
            case 'int':
                return (int)$this->defaultValue;
            case 'float':
                return (float)$this->defaultValue;
            case 'bool':
                if (strtolower($this->defaultValue) == 'false')
                {
                    return false;
                }

                return (bool)$this->defaultValue;
        }

        return (string)$this->defaultValue;
    }

    /**
     * Specify column default value. You can use magic constant Database::TIMESTAMP_NOW to specify
     * that value should gain current time on row creation, this can be applied only to timestamp fields.
     *
     * @param mixed $value
     * @return static
     */
    public function defaultValue($value)
    {
        $this->defaultValue = $value;
        if (
            $this->abstractType() == 'timestamp'
            && strtolower($value) == strtolower(Database::TIMESTAMP_NOW)
        )
        {
            $this->defaultValue = $this->table->getDriver()->timestampNow();
        }

        return $this;
    }

    /**
     * Mark column as primary key (sequence for table), attention, in most of DBMS primary key is
     * integer with auto-increment flag. If you need compound indexes, use
     * TableSchema->setPrimaryKeys(...) method.
     *
     * @return static
     */
    public function primary()
    {
        $this->table->setPrimaryKeys($this->name);

        return $this->type('primary');
    }

    /**
     * Mark column as primary key (big integer) (sequence for table), attention, in most of DBMS
     * primary key is integer with auto-increment flag. If you need compound indexes, use
     * TableSchema->setPrimaryKeys(...) method.
     *
     * @return static
     */
    public function bigPrimary()
    {
        $this->table->setPrimaryKeys($this->name);

        return $this->type('bigPrimary');
    }

    /**
     * Give column enum type with specified set of allowed values, values can be provided as array
     * or as multiple comma separate parameters. Attention, not all databases support enum as type,
     * in this cases enum will be emulated via column constrain. Enum values are always string type.
     *
     * Examples:
     * $table->status->enum(array('active', 'disabled'));
     * $table->status->enum('active', 'disabled');
     *
     * @param array|array $values Enum values (array or comma separated).
     * @return static
     */
    public function enum($values)
    {
        $this->type('enum');
        $this->enumValues = array_map('strval', is_array($values) ? $values : func_get_args());

        return $this;
    }

    /**
     * Column enum values.
     *
     * @return array
     */
    public function getEnumValues()
    {
        return $this->enumValues;
    }

    /**
     * Define string with limited max length type. Not all databases can support it, in this case
     * type will be emulated using constraint.
     * Attention, maximum allowed string length is 255 characters, if you need longer strings use text
     * types.
     *
     * This is perfect type to store email addresses as it big enough to store valid address and can
     * be covered with unique index.
     *
     * @link http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
     * @param int $size Max string length. Maximum value is 255.
     * @return static
     */
    public function string($size = 255)
    {
        $this->type('string');

        if ($size > 255)
        {
            throw new \InvalidArgumentException(
                "String size can't exceed 255 characters. Use text instead."
            );
        }

        if ($size < 0)
        {
            throw new \InvalidArgumentException("Invalid string length value.");
        }

        $this->size = (int)$size;

        return $this;
    }

    /**
     * Decimal column type with specified precision and scale.
     *
     * @param int $precision
     * @param int $scale
     * @return static
     */
    public function decimal($precision, $scale)
    {
        $this->type('decimal');

        if (!$precision)
        {
            throw new \InvalidArgumentException("Invalid precision value.");
        }

        $this->precision = (int)$precision;
        $this->scale = (int)$scale;

        return $this;
    }

    /**
     * Associate one of existed mappings with column type. Alias for ColumnSchema->type() method.
     *
     * @param string $type      Abstract or virtual type declared in mapping.
     * @param array  $arguments Not used.
     * @return static
     */
    public function __call($type, array $arguments = array())
    {
        return $this->type($type);
    }

    /**
     * Associate table index with current column.
     *
     * @return AbstractIndexSchema
     */
    public function index()
    {
        return $this->table->index($this->name);
    }

    /**
     * Associate unique table index with current column.
     *
     * @return AbstractIndexSchema
     */
    public function unique()
    {
        return $this->table->unique($this->name);
    }

    /**
     * Create foreign key constrain linked to current column and defined by foreign table and key
     * names. Attention, make sure that both columns (local and foreign) has same type (including
     * unsigned flag)
     *
     * @param string $table  Foreign table name.
     * @param string $column Foreign column name (id by default).
     * @return AbstractReferenceSchema
     * @throws SchemaBuilderException
     */
    public function foreign($table, $column = 'id')
    {
        if ($this->phpType() != 'int')
        {
            throw new SchemaBuilderException(
                "Only numeric types can be defined with foreign key constraint."
            );
        }

        return $this->table->foreign($this->name)->references($table, $column);
    }

    /**
     * Drop column from table schema. This method will also force column erasing from database table
     * schema on TableSchema->save() method call.
     */
    public function drop()
    {
        $this->table->dropColumn($this->getName());
    }

    /**
     * Compare two column schemas to check if data were altered.
     *
     * @param AbstractColumnSchema $dbColumn
     * @return bool
     */
    public function compare(AbstractColumnSchema $dbColumn)
    {
        if ($this != $dbColumn)
        {
            $columnVars = get_object_vars($this);
            $dbColumnVars = get_object_vars($dbColumn);

            $difference = array();

            foreach ($columnVars as $name => $value)
            {
                //Default values has to compared via type-casted value
                if ($name == 'defaultValue' && $this->getDefaultValue() != $dbColumn->getDefaultValue())
                {
                    $difference[] = $name;
                    continue;
                }

                if ($value != $dbColumnVars[$name])
                {
                    $difference[] = $name;
                }
            }

            self::logger()->debug("Column '{name}' has changed attributes: {difference}.", array(
                'name'       => $this->name,
                'difference' => join(', ', $difference)
            ));
        }

        return $this == $dbColumn;
    }

    /**
     * Prepare default value to be used in sql statements, string values will be quoted.
     *
     * @return string
     */
    protected function prepareDefault()
    {
        if (($defaultValue = $this->getDefaultValue()) === null)
        {
            return 'NULL';
        }

        if ($defaultValue instanceof SQLFragmentInterface)
        {
            return $defaultValue->sqlStatement();
        }

        if ($this->phpType() == 'bool')
        {
            return $defaultValue ? 'TRUE' : 'FALSE';
        }

        if ($this->phpType() == 'float')
        {
            return sprintf('%F', $defaultValue);
        }

        if ($this->phpType() == 'int')
        {
            return $defaultValue;
        }

        return $this->table->getDriver()->getPDO()->quote($defaultValue);
    }

    /**
     * Get database specific enum type definition. Should not include database type and column name.
     *
     * @return string.
     */
    protected function enumType()
    {
        $enumValues = array();
        foreach ($this->enumValues as $value)
        {
            $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
        }

        if (!empty($enumValues))
        {
            return '(' . join(', ', $enumValues) . ')';
        }

        return '';
    }

    /**
     * Compile column create statement.
     *
     * @return string
     */
    public function sqlStatement()
    {
        $statement = array($this->getName(true), $this->type);

        if ($this->abstractType() == 'enum')
        {
            if ($enumDefinition = $this->enumType())
            {
                $statement[] = $enumDefinition;
            }
        }
        elseif (!empty($this->precision))
        {
            $statement[] = "({$this->precision}, {$this->scale})";
        }
        elseif (!empty($this->size))
        {
            $statement[] = "({$this->size})";
        }

        $statement[] = $this->nullable ? 'NULL' : 'NOT NULL';

        if ($this->defaultValue !== null)
        {
            $statement[] = "DEFAULT {$this->prepareDefault()}";
        }

        return join(' ', $statement);
    }

    /**
     * Get all associated column constraints, required to perform correct column drop. Foreign keys
     * should not be included in there.
     *
     * @return array
     */
    public function getConstraints()
    {
        return array();
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->sqlStatement();
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        $column = array(
            'name' => $this->name,
            'type' => array(
                'database' => $this->type,
                'schema'   => $this->abstractType(),
                'php'      => $this->phpType()
            )
        );

        if (!empty($this->size))
        {
            $column['size'] = $this->size;
        }

        if ($this->nullable)
        {
            $column['nullable'] = true;
        }

        if ($this->defaultValue !== null)
        {
            $column['defaultValue'] = $this->getDefaultValue();
        }

        if ($this->abstractType() == 'enum')
        {
            $column['enumValues'] = $this->enumValues;
        }

        if ($this->abstractType() == 'decimal')
        {
            $column['precision'] = $this->precision;
            $column['scale'] = $this->scale;
        }

        return (object)$column;
    }
}