<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres;

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
        $this->type = strpos($schema, ' UNIQUE ') ? self::UNIQUE : self::NORMAL;

        if (preg_match('/\(([^)]+)\)/', $schema, $matches))
        {
            $this->columns = explode(',', $matches[1]);

            foreach ($this->columns as &$column)
            {
                //Postgres with add quotes to all columns with uppercase letters
                $column = trim($column, ' "\'');
                unset($column);
            }
        }
    }
}