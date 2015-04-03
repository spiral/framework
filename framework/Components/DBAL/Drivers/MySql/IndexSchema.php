<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

use Spiral\Components\DBAL\Schemas\AbstractIndexSchema;

class IndexSchema extends AbstractIndexSchema
{
    /**
     * Parse index information provided by parent TableSchema and populate index values.
     *
     * @param mixed $schema Index information fetched from database by TableSchema. Format depends
     *                      on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        foreach ($schema as $index)
        {
            $this->type = $index['Non_unique'] ? self::NORMAL : self::UNIQUE;
            $this->columns[] = $index['Column_name'];
        }
    }
}