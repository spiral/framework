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
use Spiral\Components\DBAL\Schemas\AbstractIndexSchema;
use Spiral\Components\DBAL\Schemas\AbstractReferenceSchema;
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;

class TableSchema extends AbstractTableSchema
{
    /**
     * List of most common MySQL table engines.
     */
    const ENGINE_INNODB = 'InnoDB';
    const ENGINE_MYISAM = 'MyISAM';
    const ENGINE_MEMORY = 'Memory';

    /**
     * MySQL table engine.
     *
     * @var string
     */
    protected $engine = self::ENGINE_INNODB;

    /**
     * Update table engine, due in current version we are not reading ENGINE in database and not allowing
     * engine change there is no method to retrieve current value.
     *
     * @param string $engine
     * @return static
     */
    public function engine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Driver specific method to load table columns schemas.  Method will not be called if table not
     * exists. To create and register column schema use internal table method "registerColumn()".
     **/
    protected function loadColumns()
    {
        $query = interpolate("SHOW FULL COLUMNS FROM {table}", ['table' => $this->getName(true)]);

        foreach ($this->driver->query($query)->bind(1, $columnName) as $column)
        {
            $this->registerColumn($columnName, $column);
        }
    }

    /**
     * Driver specific method to load table indexes schema(s). Method will not be called if table not
     * exists. To create and register index schema use internal table method "registerIndex()".
     */
    protected function loadIndexes()
    {
        $indexes = [];
        $query = interpolate("SHOW INDEXES FROM {table}", ['table' => $this->getName(true)]);
        foreach ($this->driver->query($query) as $index)
        {
            if ($index['Key_name'] == 'PRIMARY')
            {
                $this->primaryKeys[] = $index['Column_name'];
                $this->dbPrimaryKeys[] = $index['Column_name'];
                continue;
            }

            $indexes[$index['Key_name']][] = $index;
        }

        foreach ($indexes as $index => $schema)
        {
            $this->registerIndex($index, $schema);
        }
    }

    /**
     * Driver specific method to load table foreign key schema(s). Method will not be called if table
     * not exists. To create and register reference (foreign key) schema use internal table method
     * "registerReference()".
     */
    protected function loadReferences()
    {
        $query = "SELECT * FROM information_schema.referential_constraints "
            . "WHERE constraint_schema = ? AND table_name = ?";
        $references = $this->driver->query($query, [$this->driver->getDatabaseName(), $this->name]);

        foreach ($references as $reference)
        {
            $query = "SELECT * FROM information_schema.key_column_usage "
                . "WHERE constraint_name = ? AND table_schema = ? AND table_name = ?";

            $column = $this->driver->query($query, [
                $reference['CONSTRAINT_NAME'],
                $this->driver->getDatabaseName(),
                $this->name
            ])->fetch();

            $this->registerReference($reference['CONSTRAINT_NAME'], $reference + $column);
        }
    }

    /**
     * Generate table creation statement and execute it (if required). Method should return create
     * table sql query.
     *
     * @param bool $execute If true generated statement will be automatically executed.
     * @return string
     */
    protected function createSchema($execute = true)
    {
        $statement = parent::createSchema(false);

        //Additional table options
        $options = "ENGINE = {engine}";
        $statement = $statement . ' ' . interpolate($options, ['engine' => $this->engine]);

        //Executing
        $execute && $this->driver->statement($statement);

        if ($execute)
        {
            //Not all databases support adding index while table creation, so we can do it after
            foreach ($this->indexes as $index)
            {
                $this->doIndexAdd($index);
            }
        }

        return $statement;
    }

    /**
     * Driver specific column altering command.
     *
     * @param AbstractColumnSchema $column
     * @param AbstractColumnSchema $dbColumn
     */
    protected function doColumnChange(AbstractColumnSchema $column, AbstractColumnSchema $dbColumn)
    {
        $query = interpolate("ALTER TABLE {table} CHANGE {column} {statement}", [
            'table'     => $this->getName(true),
            'column'    => $dbColumn->getName(true),
            'statement' => $column->sqlStatement()
        ]);

        $this->driver->statement($query);
    }

    /**
     * Driver specific index remove (drop) command.
     *
     * @param AbstractIndexSchema $index
     */
    protected function doIndexDrop(AbstractIndexSchema $index)
    {
        $this->driver->statement("DROP INDEX {$index->getName(true)} ON {$this->getName(true)}");
    }

    /**
     * Driver specific index altering command, by default it will remove and add index.
     *
     * @param AbstractIndexSchema $index
     * @param AbstractIndexSchema $dbIndex
     */
    protected function doIndexChange(AbstractIndexSchema $index, AbstractIndexSchema $dbIndex)
    {
        $query = interpolate("ALTER TABLE {table} DROP INDEX {original}, ADD {statement}", [
            'table'     => $this->getName(true),
            'original'  => $dbIndex->getName(true),
            'statement' => $index->sqlStatement(false)
        ]);

        $this->driver->statement($query);
    }

    /**
     * Driver specific foreign key remove (drop) command.
     *
     * @param AbstractReferenceSchema $foreign
     */
    protected function doForeignDrop(AbstractReferenceSchema $foreign)
    {
        $this->driver->statement(
            "ALTER TABLE {$this->getName(true)} DROP FOREIGN KEY {$foreign->getName(true)}"
        );
    }
}