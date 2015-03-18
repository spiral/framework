<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Sqlite;

use Spiral\Components\DBAL\Schemas\AbstractIndexSchema;

class IndexSchema extends AbstractIndexSchema
{
    /**
     * Parse index information provided by parent TableSchema and populate index values.
     *
     * @param mixed $schema Index information fetched from database by TableSchema. Format depends on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->name = $schema['name'];
        $this->type = $schema['unique'] ? self::UNIQUE : self::NORMAL;

        foreach ($this->table->getDriver()->query("PRAGMA INDEX_INFO({$this->getName(true)})") as $column)
        {
            $this->columns[] = $column['name'];
        }
    }
}