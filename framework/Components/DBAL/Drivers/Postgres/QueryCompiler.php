<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres;

use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\QueryCompiler as BaseQueryCompiler;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * Compile insert query statement. Table name (without prefix), columns and list of rowsets is required.
     *
     * @param string $table      Table name without prefix.
     * @param array  $columns    Columns name.
     * @param array  $rowsets    List of rowsets, usually presented by Parameter instances as every rowset is array of values.
     * @param string $primaryKey Primary key name to return.
     * @return string
     * @throws DBALException
     */
    public function insert($table, array $columns, array $rowsets, $primaryKey = '')
    {
        return parent::insert($table, $columns, $rowsets) . ($primaryKey ? ' RETURNING ' . $this->quote($primaryKey) : '');
    }

    /**
     * Compile delete query statement. Table name, joins and where tokens, order by tokens, limit and order are required.
     * PostgresSQL requires nested query for ordering and limits.
     *
     * @link http://www.postgresql.org/message-id/1291109101.26137.35.camel@pcd12478
     * @param string $table
     * @param array  $joins
     * @param array  $where
     * @param array  $orderBy
     * @param int    $limit
     * @return string
     * @throws DBALException
     */
    public function delete($table, array $joins = array(), array $where = array(), array $orderBy = array(), $limit = 0)
    {
        if (!$orderBy && !$limit)
        {
            return parent::delete($table, $joins, $where);
        }

        $selection = self::select(array($table), false, array('ctid'), $joins, $where, array(), array(), $orderBy, $limit, 0);

        return self::delete($table) . " WHERE {$this->quote('ctid')} = any(array($selection))";
    }

    /**
     * Compile update query statement. Table name, set of values (associated with column names), joins and where tokens,
     * order by tokens and limit are required. Default query compiler will not compile limit and order by, it has to be
     * done on driver compiler level.
     *
     * @param string $table
     * @param array  $values
     * @param array  $joins
     * @param array  $where
     * @param array  $orderBy
     * @param int    $limit
     * @return string
     */
    public function update($table, array $values, array $joins = array(), array $where = array(), array $orderBy = array(), $limit = 0)
    {
        if (!$orderBy && !$limit)
        {
            return parent::update($table, $values, $joins, $where);
        }

        $selection = self::select(array($table), false, array('ctid'), $joins, $where, array(), array(), $orderBy, $limit, 0);

        return self::update($table, $values) . " WHERE {$this->quote('ctid')} = any(array($selection))";
    }

    /**
     * Compile DISTINCT query statement chunk. Postgres supports distinct condition.
     *
     * @param mixed $distinct
     * @return string
     */
    protected function distinct($distinct)
    {
        return 'DISTINCT' . (is_string($distinct) ? '(' . $this->quote($distinct) . ')' : '');
    }
}