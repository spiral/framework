<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

use Spiral\Components\DBAL\QueryCompiler as BaseQueryCompiler;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * Compile delete query statement. Table name, joins and where tokens, order by tokens, limit and
     * order are required. MySQL support delete limit and order.
     *
     * @param string $table
     * @param array  $joins
     * @param array  $where
     * @param array  $orderBy
     * @param int    $limit
     * @return string
     */
    public function delete(
        $table,
        array $joins = [],
        array $where = [],
        array $orderBy = [],
        $limit = 0
    )
    {
        $statement = parent::delete($table, $joins, $where, [], 0) . ' ';

        //MySQL support delete limit, offset and order in update statements.
        if (!empty($orderBy))
        {
            $statement .= $this->orderBy($orderBy) . ' ';
        }

        if (!empty($limit))
        {
            $statement .= $this->limit($limit, 0) . ' ';
        }

        return rtrim($statement);
    }

    /**
     * Compile update query statement. Table name, set of values (associated with column names), joins
     * and where tokens, order by tokens and limit are required. MySQL support update limit and order.
     *
     * @param string $table
     * @param array  $values
     * @param array  $joins
     * @param array  $where
     * @param array  $orderBy
     * @param int    $limit
     * @return string
     */
    public function update(
        $table,
        array $values,
        array $joins = [],
        array $where = [],
        array $orderBy = [],
        $limit = 0
    )
    {
        $statement = parent::update($table, $values, $joins, $where, [], 0) . ' ';

        //MySQL support update limit, offset and order in update statements.
        if (!empty($orderBy))
        {
            $statement .= $this->orderBy($orderBy) . ' ';
        }

        if (!empty($limit))
        {
            $statement .= $this->limit($limit, 0) . ' ';
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