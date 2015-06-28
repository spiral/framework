<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Schemas;

use Spiral\Core\Component;

abstract class AbstractReferenceSchema extends Component
{
    /**
     * Delete and update foreign key rules.
     */
    const CASCADE   = 'CASCADE';
    const NO_ACTION = 'NO ACTION';

    /**
     * Parent table schema.
     *
     * @invisible
     * @var AbstractTableSchema
     */
    protected $table = null;

    /**
     * Constraint name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Local column name (key name).
     *
     * @var string
     */
    protected $column = '';

    /**
     * Referenced table name (including prefix).
     *
     * @var string
     */
    protected $foreignTable = '';

    /**
     * Linked foreign key name (foreign column).
     *
     * @var string
     */
    protected $foreignKey = '';

    /**
     * Action on local column value deletion.
     *
     * @var string
     */
    protected $deleteRule = self::NO_ACTION;

    /**
     * Action on local column value update.
     *
     * @var string
     */
    protected $updateRule = self::NO_ACTION;

    /**
     * Instance on ConstraintSchema represent table foreign key, it should contain information about
     * referenced table, column name and delete/update rules.
     *
     * @param AbstractTableSchema $table
     * @param  string             $name
     * @param mixed               $schema Constraint information fetched from database by TableSchema.
     *                                    Format depends on driver type.
     */
    public function __construct(AbstractTableSchema $table, $name, $schema = null)
    {
        $this->name = $name;
        $this->table = $table;

        $schema && $this->resolveSchema($schema);
    }

    /**
     * Parse schema information provided by parent TableSchema and populate foreign key values.
     *
     * @param mixed $schema Foreign key information fetched from database by TableSchema. Format
     *                      depends on database type.
     * @return mixed
     */
    abstract protected function resolveSchema($schema);

    /**
     * Constraint name. Foreign key name can not be changed manually, while table creation name will
     * be generated automatically.
     *
     * @param bool $quoted If true constraint name will be quoted accordingly to driver rules.
     * @return string
     */
    public function getName($quoted = false)
    {
        $name = $this->name;
        if (empty($this->name))
        {
            $name = $this->table->getName() . '_foreign_' . $this->column . '_' . uniqid();
        }

        return $quoted ? $this->table->getDriver()->identifier($name) : $name;
    }

    /**
     * Get column name foreign key assigned to.
     *
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set foreign key column name, make sure column type is the same as foreign column one. Some
     * drivers will automatically create index while registering foreign key.
     *
     * @param string $column In-table column name.
     * @return static
     */
    public function column($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Set references table and key names, make sure that local column type and identical to foreign
     * one. Some drivers will automatically create index while registering foreign key.
     *
     * @param string $table  Foreign table name without prefix.
     * @param string $column Foreign key name (id by default).
     * @return static
     */
    public function references($table, $column = 'id')
    {
        $this->foreignTable = $this->table->getTablePrefix() . $table;
        $this->foreignKey = $column;

        return $this;
    }

    /**
     * Foreign table name.
     *
     * @return string
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
    }

    /**
     * Foreign key (column name).
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get delete rule, possible values: NO ACTION, CASCADE
     *
     * @return string
     */
    public function getDeleteRule()
    {
        return $this->deleteRule;
    }

    /**
     * Set foreign key delete behaviour. Originally was named "deleteRule", but it created bad
     * associations.
     *
     * @param string $rule Possible values: NO ACTION, CASCADE
     * @return static
     */
    public function onDelete($rule = self::NO_ACTION)
    {
        $this->deleteRule = strtoupper($rule);

        return $this;
    }

    /**
     * Get update rule, possible values: NO ACTION, CASCADE
     *
     * @return string
     */
    public function getUpdateRule()
    {
        return $this->updateRule;
    }

    /**
     * Set foreign key update behaviour.
     *
     * @param string $rule Possible values: NO ACTION, CASCADE
     * @return static
     */
    public function onUpdate($rule = self::NO_ACTION)
    {
        $this->updateRule = strtoupper($rule);

        return $this;
    }

    /**
     * Drop foreign key from table schema, change will be applied on next TableSchema->save() call.
     */
    public function drop()
    {
        $this->table->dropForeign($this->getName());
    }

    /**
     * Compare two foreign key schemas to check if data were altered.
     *
     * @param AbstractReferenceSchema $original
     * @return bool
     */
    public function compare(AbstractReferenceSchema $original)
    {
        return $this == $original;
    }

    /**
     * Get foreign key definition statement.
     *
     * @return string
     */
    public function sqlStatement()
    {
        $statement = [];

        $statement[] = 'CONSTRAINT';
        $statement[] = $this->getName(true);
        $statement[] = 'FOREIGN KEY';
        $statement[] = '(' . $this->table->getDriver()->identifier($this->column) . ')';

        $statement[] = 'REFERENCES ' . $this->table->getDriver()->identifier($this->foreignTable);
        $statement[] = '(' . $this->table->getDriver()->identifier($this->foreignKey) . ')';

        if ($this->deleteRule != self::NO_ACTION)
        {
            $statement[] = "ON DELETE {$this->deleteRule}";
        }

        if ($this->updateRule != self::NO_ACTION)
        {
            $statement[] = "ON UPDATE {$this->updateRule}";
        }

        return join(' ', $statement);
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
}