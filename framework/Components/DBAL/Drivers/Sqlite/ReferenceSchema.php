<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Sqlite;

use Spiral\Components\DBAL\Schemas\AbstractReferenceSchema;

class ReferenceSchema extends AbstractReferenceSchema
{
    /**
     * Parse schema information provided by parent TableSchema and populate foreign key values.
     *
     * @param mixed $schema Foreign key information fetched from database by TableSchema. Format depends
     *                      on database type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->column = $schema['from'];

        $this->foreignTable = $schema['table'];
        $this->foreignKey = $schema['to'];

        $this->deleteRule = $schema['on_delete'];
        $this->updateRule = $schema['on_update'];
    }

    /**
     * Get foreign key definition statement. SQLite has reduced syntax.
     *
     * @return string
     */
    public function sqlStatement()
    {
        $statement = array();

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
}