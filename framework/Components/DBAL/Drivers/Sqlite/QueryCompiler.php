<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Sqlite;

use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\QueryCompiler as BaseQueryCompiler;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * Compile insert query statement. Table name (without prefix), columns and list of rowsets is required.
     *
     * @param string $table   Table name without prefix.
     * @param array  $columns Columns name.
     * @param array  $rowsets List of rowsets, usually presented by Parameter instances as every rowset is array of values.
     * @return string
     * @throws DBALException
     */
    public function insert($table, array $columns, array $rowsets)
    {
        if (count($rowsets) == 1)
        {
            return parent::insert($table, $columns, $rowsets);
        }

        //SQLite uses alternative syntax
        $statement[] = "INSERT INTO {$this->quote($table, true)} ({$this->columns($columns)})";

        foreach ($rowsets as $rowset)
        {
            if (count($statement) == 1)
            {
                $selectColumns = array();
                foreach ($columns as $column)
                {
                    $selectColumns[] = "? AS {$this->quote($column)}";
                }

                $statement[] = 'SELECT ' . join(', ', $selectColumns);
            }
            else
            {
                $statement[] = 'UNION SELECT ' . trim(str_repeat('?, ', count($columns)), ', ');
            }
        }

        return join("\n", $statement);
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

        $selection = self::select(array($table), false, array('rowid'), $joins, $where, array(), array(), $orderBy, $limit, 0);

        return self::delete($table) . " WHERE {$this->quote('rowid')} IN ($selection)";
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

        $selection = self::select(array($table), false, array('rowid'), $joins, $where, array(), array(), $orderBy, $limit, 0);

        return self::update($table, $values) . " WHERE {$this->quote('rowid')} IN ($selection)";
    }

    /**
     * Render selection (affection) limit and offset. SQLite limit should be always provided (if offset not empty).
     *
     * @link http://stackoverflow.com/questions/10491492/sqllite-with-skip-offset-only-not-limit
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset)
    {
        $statement = '';

        if ($limit || $offset)
        {
            //When limit is not provided but offset does we can replace limit value with PHP_INT_MAX
            $statement = "LIMIT " . ($limit ?: '-1') . " ";
        }

        if ($offset)
        {
            $statement .= "OFFSET {$offset}";
        }

        return trim($statement);
    }
}