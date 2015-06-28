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
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;

class TableSchema extends AbstractTableSchema
{

    /**
     * Sequence object name usually defined only for primary keys and required by ORM to correctly
     * resolve inserted row id.
     *
     * @var string|null
     */
    protected $sequenceName = null;

    /**
     * Sequence object name usually defined only for primary keys and required by ORM to correctly
     * resolve inserted row id.
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequenceName;
    }

    /**
     * Driver specific method to load table columns schemas.  Method will not be called if table not
     * exists. To create and register column schema use internal table method "registerColumn()".
     **/
    protected function loadColumns()
    {
        //Required for constraints fetch
        $tableOID = $this->driver
            ->query("SELECT oid FROM pg_class WHERE relname = ?", [$this->name])
            ->fetchColumn();

        //Collecting all candidates
        $this->sequenceName = [];
        $query = "SELECT * FROM information_schema.columns
                  JOIN pg_type ON (pg_type.typname = columns.udt_name)
                  WHERE table_name = ?";

        $columns = $this->driver->query($query, [$this->name])->bind('column_name', $columnName);

        foreach ($columns as $column)
        {
            if (preg_match(
                '/^nextval\([\'"]([a-z0-9_"]+)[\'"](?:::regclass)?\)$/i',
                $column['column_default'],
                $matches
            ))
            {
                $this->sequenceName[$columnName] = $matches[1];
            }

            $this->registerColumn($columnName, $column + ['tableOID' => $tableOID]);
        }
    }

    /**
     * Driver specific method to load table indexes schema(s). Method will not be called if table not
     * exists. To create and register index schema use internal table method "registerIndex()".
     */
    protected function loadIndexes()
    {
        $query = "SELECT * FROM pg_indexes WHERE schemaname = 'public' AND tablename = ?";
        foreach ($this->driver->query($query, [$this->name]) as $index)
        {
            $index = $this->registerIndex($index['indexname'], $index['indexdef']);

            $conType = $this->driver
                ->query("SELECT contype FROM pg_constraint WHERE conname = ?", [
                    $index->getName()
                ])
                ->fetchColumn();

            if ($conType == 'p')
            {
                $this->primaryKeys = $this->dbPrimaryKeys = $index->getColumns();
                unset($this->indexes[$index->getName()], $this->dbIndexes[$index->getName()]);

                if (is_array($this->sequenceName) && count($index->getColumns()) === 1)
                {
                    $column = $index->getColumns()[0];
                    if (isset($this->sequenceName[$column]))
                    {
                        //We found our primary sequence
                        $this->sequenceName = $this->sequenceName[$column];
                    }
                }
            }
        }

        if (is_array($this->sequenceName))
        {
            //Unable to resolve
            $this->sequenceName = null;
        }
    }

    /**
     * Driver specific method to load table foreign key schema(s). Method will not be called if table
     * not exists. To create and register reference (foreign key) schema use internal table method
     * "registerReference()".
     */
    protected function loadReferences()
    {
        $query = "SELECT tc.constraint_name, tc.table_name, kcu.column_name, rc.update_rule,
                  rc.delete_rule, ccu.table_name AS foreign_table_name,
                  ccu.column_name AS foreign_column_name
                  FROM information_schema.table_constraints AS tc
                  JOIN information_schema.key_column_usage AS kcu
                      ON tc.constraint_name = kcu.constraint_name
                  JOIN information_schema.constraint_column_usage AS ccu
                      ON ccu.constraint_name = tc.constraint_name
                  JOIN information_schema.referential_constraints AS rc
                      ON rc.constraint_name = tc.constraint_name
                  WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name=?";

        foreach ($this->driver->query($query, [$this->name]) as $reference)
        {
            $this->registerReference($reference['constraint_name'], $reference);
        }
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
            $this->driver->statement(
                interpolate('ALTER TABLE {table} RENAME COLUMN {original} TO {column}',
                    [
                        'table'    => $this->getName(true),
                        'column'   => $column->getName(true),
                        'original' => $dbColumn->getName(true)
                    ]
                )
            );

            $column->setName($dbColumn->getName());
        }

        //Postgres columns should be altered using set of operations
        if (!$operations = $column->alterOperations($dbColumn))
        {
            return;
        }

        //Postgres columns should be altered using set of operations
        $query = interpolate('ALTER TABLE {table} {operations}', [
            'table'      => $this->getName(true),
            'operations' => trim(join(', ', $operations), ', ')
        ]);

        $this->driver->statement($query);
    }
}