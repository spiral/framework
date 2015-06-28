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
use Spiral\Components\DBAL\Schemas\AbstractReferenceSchema;
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;
use Spiral\Components\DBAL\Schemas\SchemaBuilderException;

class TableSchema extends AbstractTableSchema
{
    /**
     * Driver specific method to load table columns schemas.  Method will not be called if table not
     * exists. To create and register column schema use internal table method "registerColumn()".
     **/
    protected function loadColumns()
    {
        $tableSQL = $this->driver->query(
            "SELECT sql FROM sqlite_master WHERE type = 'table' and name = ?",
            [$this->name]
        )->fetchColumn();

        /**
         * There is not really many ways to get extra information about column in SQLite, let's parse
         * table schema. As mention, spiral SQLite schema reader will support fully only tables created
         * by spiral as we expecting every column definition be on new line.
         */
        $tableStatement = explode("\n", $tableSQL);

        foreach ($this->driver->query("PRAGMA TABLE_INFO({$this->getName(true)})") as $column)
        {
            if ($column['pk'])
            {
                $this->primaryKeys[] = $column['name'];
                $this->dbPrimaryKeys[] = $column['name'];
            }

            $column['tableStatement'] = $tableStatement;
            $this->registerColumn($column['name'], $column);
        }

        return true;
    }

    /**
     * Driver specific method to load table indexes schema(s). Method will not be called if table not
     * exists. To create and register index schema use internal table method "registerIndex()".
     */
    protected function loadIndexes()
    {
        foreach ($this->driver->query("PRAGMA index_list({$this->getName(true)})") as $index)
        {
            $index = $this->registerIndex($index['name'], $index);
            if ($index->getColumns() == $this->primaryKeys)
            {
                unset($this->indexes[$index->getName()], $this->dbIndexes[$index->getName()]);
            }
        }
    }

    /**
     * Driver specific method to load table foreign key schema(s). Method will not be called if table
     * not exists. To create and register reference (foreign key) schema use internal table method
     * "registerReference()".
     */
    protected function loadReferences()
    {
        foreach ($this->driver->query("PRAGMA foreign_key_list({$this->getName(true)})") as
                 $reference)
        {
            $this->registerReference($reference['id'], $reference);
        }
    }

    /**
     * Perform set of atomic operations required to update table schema, such operations will include
     * column adding, removal, altering; index adding, removing altering; foreign key constraints adding,
     * removing and altering. All operations will be performed under common transaction, failing
     * one - will rollback others. Attention, rolling back transaction with schema modifications can
     * be not implemented in some databases.
     *
     * We will have to rebuild and copy database in SQLite as it has some limitations.
     *
     * @throws SchemaBuilderException
     * @throws \Exception
     */
    protected function updateSchema()
    {
        if ($this->primaryKeys != $this->dbPrimaryKeys)
        {
            throw new SchemaBuilderException(
                "Primary keys can not be changed for already exists table."
            );
        }

        $this->driver->beginTransaction();
        try
        {
            $rebuildRequired = false;
            if ($this->alteredColumns() || $this->alteredReferences())
            {
                $rebuildRequired = true;
            }

            if (!$rebuildRequired)
            {
                foreach ($this->alteredIndexes() as $name => $schema)
                {
                    $dbIndex = isset($this->dbIndexes[$name]) ? $this->dbIndexes[$name] : null;

                    if (!$schema)
                    {
                        self::logger()->info(
                            "Dropping index [{statement}] from table {table}.", [
                            'statement' => $dbIndex->sqlStatement(true),
                            'table'     => $this->getName(true)
                        ]);

                        $this->doIndexDrop($dbIndex);
                        continue;
                    }

                    if (!$dbIndex)
                    {
                        self::logger()->info(
                            "Adding index [{statement}] into table {table}.", [
                            'statement' => $schema->sqlStatement(false),
                            'table'     => $this->getName(true)
                        ]);

                        $this->doIndexAdd($schema);
                        continue;
                    }

                    //Altering
                    self::logger()->info(
                        "Altering index [{statement}] to [{new}] in table {table}.", [
                        'statement' => $dbIndex->sqlStatement(false),
                        'new'       => $schema->sqlStatement(false),
                        'table'     => $this->getName(true)
                    ]);

                    $this->doIndexChange($schema, $dbIndex);
                }
            }
            else
            {
                self::logger()->info(
                    "Rebuilding table {table} to apply required modifications.", [
                    'table' => $this->getName(true)
                ]);

                //To be renamed later
                $tableName = $this->name;

                $this->name = 'spiral_temp_' . $this->name . '_' . uniqid();

                //SQLite index names are global
                $indexes = $this->indexes;
                $this->indexes = [];

                //Creating temporary table
                $this->createSchema();

                //Mapping columns
                $mapping = [];
                foreach ($this->columns as $name => $schema)
                {
                    if (isset($this->dbColumns[$name]))
                    {
                        $mapping[$schema->getName(true)] = $this->dbColumns[$name]->getName(true);
                    }
                }

                self::logger()->info(
                    "Migrating table data from {source} to {table} "
                    . "with columns mappings ({columns}) => ({target}).",
                    [
                        'source'  => $this->driver->identifier($tableName),
                        'table'   => $this->getName(true),
                        'columns' => join(', ', $mapping),
                        'target'  => join(', ', array_keys($mapping))
                    ]
                );

                //http://stackoverflow.com/questions/4007014/alter-column-in-sqlite
                $query = interpolate(
                    "INSERT INTO {table} ({target}) SELECT {columns} FROM {source}",
                    [
                        'source'  => $this->driver->identifier($tableName),
                        'table'   => $this->getName(true),
                        'columns' => join(', ', $mapping),
                        'target'  => join(', ', array_keys($mapping))
                    ]
                );

                $this->driver->statement($query);

                //Dropping original table
                $this->driver->statement('DROP TABLE ' . $this->driver->identifier($tableName));

                //Renaming!
                $this->rename($tableName);
                $this->name = $tableName;

                //Restoring indexes, we can create them now
                $this->indexes = $indexes;
                foreach ($this->indexes as $index)
                {
                    $this->doIndexAdd($index);
                }
            }
        }
        catch (\Exception $exception)
        {
            $this->driver->rollbackTransaction();
            throw $exception;
        }

        $this->driver->commitTransaction();
    }

    /**
     * Driver specific column add command.
     *
     * @param AbstractColumnSchema $column
     */
    protected function doColumnAdd(AbstractColumnSchema $column)
    {
        //Not supported
    }

    /**
     * Driver specific column remove (drop) command.
     *
     * @param AbstractColumnSchema $column
     */
    protected function doColumnDrop(AbstractColumnSchema $column)
    {
        //Not supported
    }

    /**
     * Driver specific column altering command.
     *
     * @param AbstractColumnSchema $column
     * @param AbstractColumnSchema $dbColumn
     */
    protected function doColumnChange(
        AbstractColumnSchema $column,
        AbstractColumnSchema $dbColumn
    )
    {
        //Not supported
    }

    /**
     * Driver specific foreign key adding command.
     *
     * @param AbstractReferenceSchema $foreign
     */
    protected function doForeignAdd(AbstractReferenceSchema $foreign)
    {
        //Not supported
    }

    /**
     * Driver specific foreign key remove (drop) command.
     *
     * @param AbstractReferenceSchema $foreign
     */
    protected function doForeignDrop(AbstractReferenceSchema $foreign)
    {
        //Not supported
    }

    /**
     * Driver specific foreign key altering command, by default it will remove and add foreign key.
     *
     * @param AbstractReferenceSchema $foreign
     * @param AbstractReferenceSchema $dbForeign
     */
    protected function doForeignChange(
        AbstractReferenceSchema $foreign,
        AbstractReferenceSchema $dbForeign
    )
    {
        //Not supported
    }
}