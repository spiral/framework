<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres;

use Spiral\Components\DBAL\Schemas\BaseIndexSchema;

class IndexSchema extends BaseIndexSchema
{
    /**
     * Parse index information provided by parent TableSchema and populate index values.
     *
     * @param mixed $schema Index information fetched from database by TableSchema. Format depends on driver type.
     * @return mixed
     */
    protected function resolveSchema($schema)
    {
        $this->type = strpos($schema, ' UNIQUE ') ? self::UNIQUE : self::NORMAL;

        if (preg_match('/\(([^)]+)\)/', $schema, $matches))
        {
            $this->columns = array_map('trim', explode(',', $matches[1]));
        }
    }
}