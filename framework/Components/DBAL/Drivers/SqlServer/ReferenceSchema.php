<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\SqlServer;

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
        $this->column = $schema['FKCOLUMN_NAME'];
        $this->foreignTable = $schema['PKTABLE_NAME'];
        $this->foreignKey = $schema['PKCOLUMN_NAME'];

        $this->deleteRule = $schema['DELETE_RULE'] ? self::NO_ACTION : self::CASCADE;
        $this->updateRule = $schema['UPDATE_RULE'] ? self::NO_ACTION : self::CASCADE;
    }
}