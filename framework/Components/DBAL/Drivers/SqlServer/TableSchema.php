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
use Spiral\Components\DBAL\Schemas\AbstractIndexSchema;
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;

class TableSchema extends AbstractTableSchema
{
    /**
     * Rename SQL statement is usually the same...
     */
    const RENAME_STATEMENT = "sp_rename @objname = '{table}', @newname = '{name}'";

    /**
     * Driver specific method to load table columns schemas.  Method will not be called if table not
     * exists. To create and register column schema use internal table method "registerColumn()".
     **/
    protected function loadColumns()
    {
        $query = 'SELECT * FROM information_schema.columns INNER JOIN sys.columns AS sysColumns
                    ON (
                        object_name(object_id) = table_name AND sysColumns.name = COLUMN_NAME
                  ) WHERE table_name = ?';
        foreach ($this->driver->query($query, array($this->name)) as $column)
        {
            $this->registerColumn($column['COLUMN_NAME'], $column);
        }
    }

    /**
     * Driver specific method to load table indexes schema(s). Method will not be called if table not
     * exists. To create and register index schema use internal table method "registerIndex()".
     *
     * @link http://stackoverflow.com/questions/765867/list-of-all-index-index-columns-in-sql-server-db
     */
    protected function loadIndexes()
    {
        $query = "SELECT indexes.name AS indexName, cl.name AS columnName,
                  is_primary_key AS isPrimary, is_unique AS isUnique
                  FROM sys.indexes AS indexes
                  INNER JOIN sys.index_columns as columns
                    ON indexes.object_id = columns.object_id AND indexes.index_id = columns.index_id
                  INNER JOIN sys.columns AS cl
                    ON columns.object_id = cl.object_id AND columns.column_id = cl.column_id
                  INNER JOIN sys.tables AS t
                    ON indexes.object_id = t.object_id
                  WHERE t.name = ?
                  ORDER BY indexes.name, indexes.index_id, columns.index_column_id";

        $indexes = array();
        foreach ($this->driver->query($query, array($this->name)) as $index)
        {
            if ($index['isPrimary'])
            {
                $this->primaryKeys[] = $index['columnName'];
                $this->dbPrimaryKeys[] = $index['columnName'];
                continue;
            }

            $indexes[$index['indexName']][] = $index;
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
        foreach ($this->driver->query("sp_fkeys @fktable_name = ?", array($this->name)) as $reference)
        {
            $this->registerReference($reference['FK_NAME'], $reference);
        }
    }

    /**
     * Driver specific column add command.
     *
     * @param AbstractColumnSchema $column
     */
    protected function doColumnAdd(AbstractColumnSchema $column)
    {
        $this->driver->statement("ALTER TABLE {$this->getName(true)} ADD {$column->sqlStatement()}");
    }

    /**
     * Driver specific column altering command.
     *
     * @param AbstractColumnSchema $column
     * @param AbstractColumnSchema $dbColumn
     */
    protected function doColumnChange(AbstractColumnSchema $column, AbstractColumnSchema $dbColumn)
    {
        /**
         * @var ColumnSchema $column
         */

        //Renaming is separate operation
        if ($column->getName() != $dbColumn->getName())
        {
            $this->driver->statement("sp_rename ?, ?, 'COLUMN'", array(
                $this->getName() . '.' . $dbColumn->getName(),
                $column->getName()
            ));

            $column->setName($dbColumn->getName());
        }

        //In SQLServer we have to drop ALL related indexes and foreign keys while
        //applying type change... yeah...
        $indexesBackup = array();
        $foreignBackup = array();
        foreach ($this->indexes as $index)
        {
            if (in_array($column->getName(), $index->getColumns()))
            {
                $indexesBackup[] = $index;
                $this->doIndexDrop($index);
            }
        }

        foreach ($this->references as $foreign)
        {
            if ($foreign->getColumn() == $column->getName())
            {
                $foreignBackup[] = $foreign;
                $this->doForeignDrop($foreign);
            }
        }

        //Column will recreate needed constraints
        foreach ($column->getConstraints() as $constraint)
        {
            $this->doConstraintDrop($constraint);
        }

        foreach ($column->alterOperations($dbColumn) as $operation)
        {
            $query = interpolate('ALTER TABLE {table} {operation}', array(
                'table'     => $this->getName(true),
                'operation' => $operation
            ));

            $this->driver->statement($query);
        }

        //Recreating indexes
        foreach ($indexesBackup as $index)
        {
            $this->doIndexAdd($index);
        }

        foreach ($foreignBackup as $foreign)
        {
            $this->doForeignAdd($foreign);
        }
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
}