<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\SqlServer;

use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;
use Spiral\Helpers\StringHelper;

class ColumnSchema extends AbstractColumnSchema
{
    /**
     * Direct mapping from base abstract type to database internal type with specified data options, such as size, precision
     * scale, unsigned flag and etc. Every declared type can be assigned using ->type() method, however to pass custom
     * type parameters, methods has to be declared in database specific ColumnSchema. Type identifier not necessary
     * should be real type name.
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
        'primary'     => array('type' => 'int', 'identity' => true),
        'bigPrimary'  => array('type' => 'bigint', 'identity' => true),

        //Enum type (mapped via method)
        'enum'        => 'enum',

        //Logical types
        'boolean'     => 'bit',

        //Integer types (size can always be changed with size method), longInteger has method alias bigInteger
        'integer'     => 'int',
        'tinyInteger' => 'tinyint',
        'bigInteger'  => 'bigint',

        //String with specified length (mapped via method)
        'string'      => 'varchar',

        //Generic types
        'text'        => array('type' => 'varchar', 'size' => 0),
        'tinyText'    => array('type' => 'varchar', 'size' => 0),
        'longText'    => array('type' => 'varchar', 'size' => 0),

        //Real types
        'double'      => 'float',
        'float'       => 'real',

        //Decimal type (mapped via method)
        'decimal'     => 'decimal',

        //Date and Time types
        'datetime'    => 'datetime',
        'date'        => 'date',
        'time'        => 'time',
        'timestamp'   => 'datetime',

        //Binary types
        'binary'      => array('type' => 'varbinary', 'size' => 0),
        'tinyBinary'  => array('type' => 'varbinary', 'size' => 0),
        'longBinary'  => array('type' => 'varbinary', 'size' => 0),

        //Additional types
        'json'        => array('type' => 'varchar', 'size' => 0)
    );

    /**
     * Driver specific reverse mapping, this mapping should link database type to one of standard internal types. Not
     * resolved types will be marked as "unknown" which will map them as php type string.
     *
     * @invisible
     * @var array
     */
    protected $reverseMapping = array(
        'primary'     => array(array('type' => 'int', 'identity' => true)),
        'bigPrimary'  => array(array('type' => 'bigint', 'identity' => true)),
        'enum'        => array('enum'),
        'boolean'     => array('bit'),
        'integer'     => array('int'),
        'tinyInteger' => array('tinyint', 'smallint'),
        'bigInteger'  => array('bigint'),
        'text'        => array(array('type' => 'varchar', 'size' => 0)),
        'string'      => array('varchar', 'char'),
        'double'      => array('float'),
        'float'       => array('real'),
        'decimal'     => array('decimal'),
        'timestamp'   => array('datetime'),
        'date'        => array('date'),
        'time'        => array('time'),
        'binary'      => array('varbinary'),
    );

    /**
     * If field table identity.
     *
     * @var bool
     */
    protected $identity = false;


    /**
     * Name of default constraint.
     *
     * @var string
     */
    protected $defaultConstraint = '';

    /**
     * Name of enum constraint.
     *
     * @var string
     */
    protected $enumConstraint = '';

    /**
     * Parse column information provided by parent TableSchema and populate column values.
     *
     * @param mixed $schema Column information fetched from database by TableSchema. Format depends on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->type = $schema['DATA_TYPE'];
        $this->nullable = strtoupper($schema['IS_NULLABLE']) == 'YES';
        $this->defaultValue = $schema['COLUMN_DEFAULT'];

        $this->identity = (bool)$schema['is_identity'];

        $this->size = (int)$schema['CHARACTER_MAXIMUM_LENGTH'];
        if ($this->size == -1)
        {
            $this->size = 0;
        }

        if ($this->type == 'decimal')
        {
            $this->precision = (int)$schema['NUMERIC_PRECISION'];
            $this->scale = (int)$schema['NUMERIC_SCALE'];
        }

        //Processing default value
        if ($this->defaultValue[0] == '(' && $this->defaultValue[strlen($this->defaultValue) - 1] == ')')
        {
            $this->defaultValue = substr($this->defaultValue, 1, -1);
        }

        if (preg_match('/^[\'""].*?[\'"]$/', $this->defaultValue))
        {
            $this->defaultValue = substr($this->defaultValue, 1, -1);
        }

        if (($this->phpType() != 'string') && ($this->defaultValue[0] == '(' && $this->defaultValue[strlen($this->defaultValue) - 1] == ')'))
        {
            $this->defaultValue = substr($this->defaultValue, 1, -1);
        }

        /**
         * We have to fetch all column constrains cos default and enum check will be included into them, plus column drop
         * is not possible without removing all constraints.
         */

        $tableDriver = $this->table->getDriver();
        if ($schema['default_object_id'])
        {
            $this->defaultConstraint = $tableDriver->query("SELECT name FROM sys.default_constraints WHERE object_id = ?", array(
                $schema['default_object_id']
            ))->fetchColumn();
        }

        //Potential enum
        if ($this->type == 'varchar' && $this->size)
        {
            $query = "SELECT object_definition(o.object_id) AS [definition], OBJECT_NAME(o.OBJECT_ID) AS [name] FROM sys.objects AS o
            join sys.sysconstraints AS [c] on o.object_id = [c].constid
            WHERE type_desc = 'CHECK_CONSTRAINT' AND parent_object_id = ? AND [c].colid = ?";

            foreach ($tableDriver->query($query, array($schema['object_id'], $schema['column_id'])) as $checkConstraint)
            {
                $this->enumConstraint = $checkConstraint['name'];

                $name = preg_quote($this->getName(true));

                //We made some assumptions here...
                if (preg_match_all('/' . $name . '=[\']?([^\']+)[\']?/i', $checkConstraint['definition'], $matches))
                {
                    $this->enumValues = $matches[1];
                    sort($this->enumValues);
                }
            }
        }
    }

    /**
     * Get abstract type name, this method will map one of database types to limited set of ColumnSchema abstract types.
     * Attention, this method is not used for schema comparasions (database type used), it's only for decorative purposes.
     * If schema can't resolve type - "unknown" will be returned (by default mapped to php type string).
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
     * Give column enum type with specified set of allowed values, values can be provided as array or as multiple comma
     * separate parameters. Attention, not all databases support enum as type, in this cases enum will be emulated via
     * column constrain. Enum values are always string type.
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
        sort($this->enumValues);

        $this->type = 'varchar';
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
     * @param bool $temporary If true enumConstraint identifier will be generated only for visual purposes.
     * @return string
     */
    protected function getEnumConstraint($quote = false, $temporary = false)
    {
        if (!$this->enumConstraint)
        {
            if ($temporary)
            {
                return $this->table->getName() . '_' . $this->getName() . '_enum_check';
            }

            $this->enumConstraint = $this->table->getName() . '_' . $this->getName() . '_enum_check_' . uniqid();
        }

        return $quote ? $this->table->getDriver()->identifier($this->enumConstraint) : $this->enumConstraint;
    }

    /**
     * Prepare default value to be used in sql statements, string values will be quoted.
     *
     * @return string
     */
    protected function prepareDefault()
    {
        $defaultValue = parent::prepareDefault();
        if ($this->abstractType() == 'boolean')
        {
            $defaultValue = (int)$this->defaultValue;
        }

        return $defaultValue;
    }

    /**
     * Compile column create statement.
     *
     * @param bool $enum Bypass enum statement condition.
     * @return string
     */
    public function sqlStatement($enum = false)
    {
        if ($enum || $this->abstractType() != 'enum')
        {
            $statement = array($this->getName(true), $this->type);

            if ($this->precision)
            {
                $statement[] = "({$this->precision}, {$this->scale})";
            }
            elseif ($this->size)
            {
                $statement[] = "({$this->size})";
            }
            elseif ($this->type == 'varchar' || $this->type == 'varbinary')
            {
                $statement[] = "(max)";
            }

            if ($this->identity)
            {
                $statement[] = 'IDENTITY(1,1)';
            }

            $statement[] = $this->nullable ? 'NULL' : 'NOT NULL';

            if ($this->defaultValue !== null)
            {
                $statement[] = "DEFAULT {$this->prepareDefault()}";
            }

            return join(' ', $statement);
        }

        //We have add constraint for enum type
        $enumValues = array();
        foreach ($this->enumValues as $value)
        {
            $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
        }

        $statement = $this->sqlStatement(true);

        return "$statement CONSTRAINT {$this->getEnumConstraint(true, true)} CHECK ({$this->getName(true)} IN (" . join(', ', $enumValues) . "))";
    }

    /**
     * Get all column constraints.
     *
     * @return array
     */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();

        if ($this->defaultConstraint)
        {
            $constraints[] = $this->defaultConstraint;
        }

        if ($this->enumConstraint)
        {
            $constraints[] = $this->enumConstraint;
        }

        return $constraints;
    }

    /**
     * Generate set of altering operations should be applied to column to change it's type, size, default value or null flag.
     *
     * @param AbstractColumnSchema $original
     * @return array
     */
    public function alterOperations(AbstractColumnSchema $original)
    {
        $operations = array();

        $typeDefinition = array($this->type, $this->size, $this->precision, $this->scale, $this->nullable);
        $originalType = array($original->type, $original->size, $original->precision, $original->scale, $original->nullable);

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

                $type = "ALTER COLUMN {$this->getName(true)} varchar($enumSize)";
                $operations[] = $type . ' ' . ($this->nullable ? 'NULL' : 'NOT NULL');
            }
            else
            {
                $type = "ALTER COLUMN {$this->getName(true)} {$this->type}";

                if ($this->size)
                {
                    $type .= "($this->size)";
                }
                elseif ($this->precision)
                {
                    $type .= "($this->precision, $this->scale)";
                }

                $operations[] = $type . ' ' . ($this->nullable ? 'NULL' : 'NOT NULL');
            }
        }

        //Constraint should be already removed it this moment (see doColumnChange in TableSchema)
        if ($this->defaultValue !== null)
        {
            if (!$this->defaultConstraint)
            {
                //Making new name
                $this->defaultConstraint = $this->table->getName() . '_' . $this->getName() . '_default_' . uniqid();
            }

            $operations[] = StringHelper::interpolate("ADD CONSTRAINT {constraint} DEFAULT {default} FOR {column}", array(
                'constraint' => $this->table->getDriver()->identifier($this->defaultConstraint),
                'column'     => $this->getName(true),
                'default'    => $this->prepareDefault()
            ));
        }

        //Constraint should be already removed it this moment (see doColumnChange in TableSchema)
        if ($this->abstractType() == 'enum')
        {
            $enumValues = array();
            foreach ($this->enumValues as $value)
            {
                $enumValues[] = $this->table->getDriver()->getPDO()->quote($value);
            }

            $operations[] = "ADD CONSTRAINT {$this->getEnumConstraint(true)} CHECK ({$this->getName(true)} IN (" . join(', ', $enumValues) . "))";
        }

        return $operations;
    }
}