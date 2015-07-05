<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

use Spiral\Components\DBAL\Builders\SelectQuery;
use Spiral\Components\DBAL\QueryCompiler as BaseQueryCompiler;
use Spiral\Components\DBAL\SqlFragmentInterface;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * Create valid list of parameters (valid order) based on query type.
     *
     * @param int   $type Query type.
     * @param array $where
     * @param array $joins
     * @param array $having
     * @param array $columns
     * @return array
     */
    public function prepareParameters(
        $type,
        array $where = [],
        $joins = [],
        array $having = [],
        array $columns = []
    )
    {
        if ($type == self::UPDATE_QUERY)
        {
            //Where statement has pretty specific order
            return array_merge($joins, $columns, $where);
        }

        return parent::prepareParameters($type, $where, $joins, $having, $columns);
    }

    /**
     * Compile delete query statement. Table name, joins and where tokens are required.
     *
     * @param string $table
     * @param array  $joins
     * @param array  $where
     * @return string
     */
    public function delete($table, array $joins = [], array $where = [])
    {
        $alias = $table;
        if (preg_match('/ as /i', $alias, $matches))
        {
            list(, $alias) = explode($matches[0], $table);
        }
        else
        {
            $table = "{$table} AS {$table}";
        }

        $statement = 'DELETE ' . $this->quote($alias) . ".*\n"
            . 'FROM ' . $this->quote($table, true, true) . ' ';

        if (!empty($joins))
        {
            $statement .= $this->joins($joins) . ' ';
        }

        if (!empty($where))
        {
            $statement .= "\nWHERE " . $this->where($where);
        }

        return rtrim($statement);
    }

    /**
     * Compile update query statement. Table name, set of values (associated with column names), joins
     * and where tokens are required.
     *
     * @param string $table
     * @param array  $columns
     * @param array  $joins
     * @param array  $where
     * @return string
     */
    public function update($table, array $columns, array $joins = [], array $where = [])
    {
        if (empty($joins))
        {
            return parent::update($table, $columns, $joins, $where);
        }

        $alias = $table;
        if (preg_match('/ as /i', $alias, $matches))
        {
            list(, $alias) = explode($matches[0], $table);
        }
        else
        {
            $table = "{$table} AS {$table}";
        }

        $statement = "UPDATE " . $this->quote($table, true, true);

        if (!empty($joins))
        {
            $statement .= $this->joins($joins) . "\n";
        }

        $statement .= "\nSET" . $this->prepareColumns($columns, $alias);

        if (!empty($where))
        {
            $statement .= "\nWHERE " . $this->where($where);
        }

        return rtrim($statement);
    }

    /**
     * Render selection (affection) limit and offset. MySQL limit should be always provided (if offset
     * not empty). See not really great bypass way from official documentation.
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/select.html#id4651990
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset)
    {
        $statement = '';

        if (!empty($limit) || !empty($offset))
        {
            //When limit is not provided but offset does we can replace limit value with PHP_INT_MAX
            $statement = "LIMIT " . ($limit ?: '18446744073709551615') . ' ';
        }

        if (!empty($offset))
        {
            $statement .= "OFFSET {$offset}";
        }

        return trim($statement);
    }
}