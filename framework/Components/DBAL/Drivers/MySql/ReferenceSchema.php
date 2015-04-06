<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

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
        $this->column = $schema['COLUMN_NAME'];

        $this->foreignTable = $schema['REFERENCED_TABLE_NAME'];
        $this->foreignKey = $schema['REFERENCED_COLUMN_NAME'];

        $this->deleteRule = $schema['DELETE_RULE'];
        $this->updateRule = $schema['UPDATE_RULE'];
    }
}