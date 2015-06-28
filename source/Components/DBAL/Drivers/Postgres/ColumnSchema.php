<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres;

use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;
use Spiral\Components\DBAL\SqlFragment;

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
        'primary'     => ['type' => 'serial', 'autoIncrement' => true],
        'bigPrimary'  => ['type' => 'bigserial', 'autoIncrement' => true],

        //Enum type (mapped via method)
        'enum'        => 'enum',

        //Logical types
        'boolean'     => 'boolean',

        //Integer types (size can always be changed with size method), longInteger has method alias
        //bigInteger
        'integer'     => 'integer',
        'tinyInteger' => 'smallint',
        'bigInteger'  => 'bigint',

        //String with specified length (mapped via method)
        'string'      => 'character varying',

        //Generic types
        'text'        => 'text',
        'tinyText'    => 'text',
        'longText'    => 'text',

        //Real types
        'double'      => 'double precision',
        'float'       => 'real',

        //Decimal type (mapped via method)
        'decimal'     => 'numeric',

        //Date and Time types
        'datetime'    => 'timestamp without time zone',
        'date'        => 'date',
        'time'        => 'time',
        'timestamp'   => 'timestamp without time zone',

        //Binary types
        'binary'      => 'bytea',
        'tinyBinary'  => 'bytea',
        'longBinary'  => 'bytea',

        //Additional types
        'json'        => 'json'
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
        'primary'     => ['serial'],
        'bigPrimary'  => ['bigserial'],
        'enum'        => ['enum'],
        'boolean'     => ['boolean'],
        'integer'     => ['int', 'integer', 'int4'],
        'tinyInteger' => ['smallint'],
        'bigInteger'  => ['bigint', 'int8'],
        'string'      => ['character varying', 'character'],
        'text'        => ['text'],
        'double'      => ['double precision'],
        'float'       => ['real', 'money'],
        'decimal'     => ['numeric'],
        'date'        => ['date'],
        'time'        => ['time', 'time with time zone', 'time without time zone'],
        'timestamp'   => ['timestamp', 'timestamp with time zone', 'timestamp without time zone'],
        'binary'      => ['bytea'],
        'json'        => ['json']
    ];

    /**
     * Field is auto incremental.
     *
     * @var bool
     */
    protected $autoIncrement = false;

    /**
     * Name of enum constraint.
     *
     * @var string
     */
    protected $enumConstraint = '';

    /**
     * Parse column information provided by parent TableSchema and populate column values.
     *
     * @param mixed $schema Column information fetched from database by TableSchema. Format depends
     *                      on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->type = $schema['data_type'];
        $this->defaultValue = $schema['column_default'];
        $this->nullable = $schema['is_nullable'] == 'YES';

        if (
            in_array($this->type, ['int', 'bigint', 'integer'])
            && preg_match("/nextval(.*)/", $this->defaultValue)
        )
        {
            $this->type = ($this->type == 'bigint' ? 'bigserial' : 'serial');
            $this->autoIncrement = true;

            $this->defaultValue = new SqlFragment($this->defaultValue);

            return;
        }

        if (
            (
                $this->type == 'character varying' || $this->type == 'character'
            )
            && $schema['character_maximum_length']
        )
        {
            $this->size = $schema['character_maximum_length'];
        }

        if ($this->type == 'numeric')
        {
            $this->precision = $schema['numeric_precision'];
            $this->scale = $schema['numeric_scale'];
        }

        /**
         * Attention, this is not default spiral enum type emulated via CHECK. This is real Postgres
         * enum type.
         */
        if ($this->type == 'USER-DEFINED' && $schema['typtype'] == 'e')
        {
            $this->type = $schema['typname'];
            $range = $this->table->getDriver()
                ->query('SELECT enum_range(NULL::' . $this->type . ')')
                ->fetchColumn(0);

            $this->enumValues = explode(',', substr($range, 1, -1));

            if (!empty($this->defaultValue))
            {
                //In database: 'value'::enumType
                $this->defaultValue = substr(
                    $this->defaultValue,
                    1,
                    strpos($this->defaultValue, $this->type) - 4
                );
            }
        }

        //Potential enum with manually created constraint (check in)
        if (
            (
                $this->type == 'character' || $this->type == 'character varying'
            )
            && $this->size
        )
        {
            $query = "SELECT conname, consrc FROM pg_constraint
                      WHERE conrelid = ? AND contype = 'c' AND consrc LIKE ?";

            $constraints = $this->table->getDriver()
                ->query($query, [$schema['tableOID'], '(' . $this->name . '%']);

            foreach ($constraints as $constraint)
            {
                if (preg_match('/ARRAY\[([^\]]+)\]/', $constraint['consrc'], $matches))
                {
                    $enumValues = explode(',', $matches[1]);
                    foreach ($enumValues as &$value)
                    {
                        if (preg_match("/^'?(.*?)'?::(.+)/", trim($value), $matches))
                        {
                            //In database: 'value'::TYPE
                            $value = $matches[1];
                        }

                        unset($value);
                    }

                    $this->enumValues = $enumValues;
                    $this->enumConstraint = $constraint['conname'];
                }
            }
        }

        if ($this->defaultValue !== null)
        {
            if (preg_match("/^'?(.*?)'?::(.+)/", $this->defaultValue, $matches))
            {
                //In database: 'value'::TYPE
                $this->defaultValue = $matches[1];
            }
            elseif ($this->type == 'bit')
            {
                $this->defaultValue = bindec(
                    substr($this->defaultValue, 2, strpos($this->defaultValue, '::') - 3)
                );
            }
            elseif ($this->type == 'boolean')
            {
                $this->defaultValue = (strtolower($this->defaultValue) == 'true');
            }
        }
    }

    /**
     * Get abstract type name, this method will map one of database types to limited set of ColumnSchema
     * abstract types. Attention, this method is not used for schema comparasions (database type used),
     * it's only for decorative purposes. If schema can't resolve type - "unknown" will be returned
     * (by default mapped to php type string).
     *
     * @return string
     */
    public function abstractType()
    {
        if ($this->enumValues)
        {
            return 'enum';
        }

        return parent::abstractType();
    }

    /**
     * Set column to be primary sequence.
     *
     * @return static
     */
    public function primary()
    {
        $this->autoIncrement = true;

        //Changing type of already created primary key (we can't use "serial" alias here)
        if ($this->type && $this->type != 'serial')
        {
            $this->type = 'integer';

            return $this;
        }

        return parent::primary();
    }

    /**
     * Set column to be big primary sequence.
     *
     * @return static
     */
    public function bigPrimary()
    {
        $this->autoIncrement = true;

        //Changing type of already created primary key (we can't use "serial" alias here)
        if ($this->type && $this->type != 'bigserial')
        {
            $this->type = 'bigint';

            return $this;
        }

        return parent::bigPrimary();
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
        $this->enumValues = array_map('strval', is_array($values) ? $values : func_get_args());

        $this->type = 'character';
        foreach ($this->enumValues as $value)
        {
            $this->size = max((int)$this->size, strlen($value));
        }

        return $this;
    }

    /**
     * Get name of enum constraint.
     *
     * @param bool $quote     True to quote identifier.
     * @param bool $temporary If true enumConstraint identifier will be generated only for visual
     *                        purposes.
     * @return string
     */
    protected function getEnumConstraint($quote = false, $temporary = false)
    {
        if (!$this->enumConstraint)
        {
            if ($temporary)
            {
                return $this->table->getName() . '_' . $this->getName() . '_enum';
            }

            $this->enumConstraint = $this->table->getName() . '_'
                . $this->getName() . '_enum_' . uniqid();
        }

        return $quote
            ? $this->table->getDriver()->identifier($this->enumConstraint)
            : $this->enumConstraint;
    }

    /**
     * Get database specific enum type definition. Should not include database type and column name.
     *
     * @return string.
     */
    protected function enumType()
    {
        return '(' . $this->size . ')';
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

        //We have add constraint for enum type
        $enumValues = [];
        foreach ($this->enumValues as $value)
        {
            $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
        }

        return "$statement CONSTRAINT {$this->getEnumConstraint(true, true)} "
        . "CHECK ({$this->getName(true)} IN (" . join(', ', $enumValues) . "))";
    }

    /**
     * Get all column constraints.
     *
     * @return array
     */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();

        if ($this->enumConstraint)
        {
            $constraints[] = $this->enumConstraint;
        }

        return $constraints;
    }

    /**
     * Generate set of altering operations should be applied to column to change it's type, size,
     * default value or null flag.
     *
     * @param AbstractColumnSchema $original
     * @return array
     */
    public function alterOperations(AbstractColumnSchema $original)
    {
        $operations = [];

        $typeDefinition = [$this->type, $this->size, $this->precision, $this->scale];
        $originalType = [$original->type, $original->size, $original->precision, $original->scale];

        if ($typeDefinition != $originalType)
        {
            if ($this->abstractType() == 'enum')
            {
                //Getting longest value
                $enumSize = $this->size;
                foreach ($this->enumValues as $value)
                {
                    $enumSize = max($enumSize, strlen($value));
                }

                $type = "ALTER COLUMN {$this->getName(true)} TYPE character($enumSize)";
                $operations[] = $type;
            }
            else
            {
                $type = "ALTER COLUMN {$this->getName(true)} TYPE {$this->type}";

                if ($this->size)
                {
                    $type .= "($this->size)";
                }
                elseif ($this->precision)
                {
                    $type .= "($this->precision, $this->scale)";
                }

                //Required to perform cross conversion
                $operations[] = "{$type} USING {$this->getName(true)}::{$this->type}";
            }
        }

        if ($original->abstractType() == 'enum' && $this->enumConstraint)
        {
            $operations[] = 'DROP CONSTRAINT ' . $this->getEnumConstraint(true);
        }

        if ($original->defaultValue != $this->defaultValue)
        {
            if (is_null($this->defaultValue))
            {
                $operations[] = "ALTER COLUMN {$this->getName(true)} DROP DEFAULT";
            }
            else
            {
                $operations[] = "ALTER COLUMN {$this->getName(true)} SET DEFAULT {$this->prepareDefault()}";
            }
        }

        if ($original->nullable != $this->nullable)
        {
            $operations[] = "ALTER COLUMN {$this->getName(true)} "
                . (!$this->nullable ? 'SET' : 'DROP') . " NOT NULL";
        }

        if ($this->abstractType() == 'enum')
        {
            $enumValues = [];
            foreach ($this->enumValues as $value)
            {
                $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
            }

            $operations[] = "ADD CONSTRAINT {$this->getEnumConstraint(true)} "
                . "CHECK ({$this->getName(true)} IN (" . join(', ', $enumValues) . "))";
        }

        return $operations;
    }
}