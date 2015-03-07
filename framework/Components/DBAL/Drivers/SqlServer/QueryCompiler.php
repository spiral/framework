<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\SqlServer;

use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\QueryCompiler as BaseQueryCompiler;
use Spiral\Components\DBAL\SqlFragment;
use Spiral\Core\Component\LoggerTrait;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * Logging.
     */
    use LoggerTrait;

    /**
     * Parent driver instance, driver used only for identifier() methods but can be required in other cases.
     *
     * @var SQLServerDriver
     */
    protected $driver = null;

    /**
     * Compile select query statement. Table names, distinct flag, columns, joins, where tokens, having tokens, group by
     * tokens (yeah, it's very big list), order by tokens, limit, offset values and unions are required. While compilation
     * table aliases will be collected from join and table parts, which will allow their usage in every condition even if
     * tablePrefix not empty.
     *
     * Attention, limiting and ordering UNIONS will fail in SQL SERVER < 2012.
     *
     * For future upgrades: think about using top command.
     *
     * @link http://stackoverflow.com/questions/603724/how-to-implement-limit-with-microsoft-sql-server
     * @link http://stackoverflow.com/questions/971964/limit-10-20-in-sql-server
     * @param array   $from
     * @param boolean $distinct
     * @param array   $columns
     * @param array   $joins
     * @param array   $where
     * @param array   $having
     * @param array   $groupBy
     * @param array   $orderBy
     * @param int     $limit
     * @param int     $offset
     * @param array   $unions
     * @return string
     * @throws DBALException
     */
    public function select(array $from, $distinct, array $columns, array $joins = array(), array $where = array(), array $having = array(),
                           array $groupBy = array(), array $orderBy = array(), $limit = 0, $offset = 0, array $unions = array())
    {
        if (!$limit && !$offset || ($this->driver->getServerVersion() >= 12 && $orderBy))
        {
            return parent::select($from, $distinct, $columns, $joins, $where, $having, $groupBy, $orderBy, $limit, $offset, $unions);
        }

        if ($this->driver->getServerVersion() >= 12)
        {
            $this->logger()->warning("You can't use query limiting without specifying ORDER BY statement, sql fallback used.");
        }
        else
        {
            $this->logger()->warning("You are using older version of SQLServer, it has some limitation with query limiting and unions.");
        }

        if ($orderBy)
        {
            $orderBy = $this->orderBy($orderBy);
        }
        else
        {
            $orderBy = "ORDER BY (SELECT NULL)";
        }

        //Will be removed by QueryResult
        $columns[] = SqlFragment::make("ROW_NUMBER() OVER ($orderBy) AS " . $this->quote(QueryResult::ROW_NUMBER_COLUMN));

        $selection = parent::select($from, $distinct, $columns, $joins, $where, $having, $groupBy, array(), 0, 0, $unions);

        return "SELECT * FROM (\n{$selection}\n) AS [selection_alias] " . $this->limit($limit, $offset, QueryResult::ROW_NUMBER_COLUMN);
    }

    /**
     * Compile delete query statement. Table name, joins and where tokens, order by tokens, limit and order are required.
     * PostgresSQL requires nested query for ordering and limits.
     *
     * @link http://stackoverflow.com/questions/3439110/sql-server-update-a-table-by-using-order-by
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

        $cte = "WITH cte AS (" . self::select(array($table), false, array('*'), $joins, $where, array(), array(), $orderBy, $limit, 0) . ") ";

        return $cte . self::delete(SqlFragment::make("cte"));
    }

    /**
     * Compile update query statement. Table name, set of values (associated with column names), joins and where tokens,
     * order by tokens and limit are required. Default query compiler will not compile limit and order by, it has to be
     * done on driver compiler level.
     *
     * @link http://stackoverflow.com/questions/3439110/sql-server-update-a-table-by-using-order-by
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

        $cte = "WITH cte AS (" . self::select(array($table), false, array_keys($values), $joins, $where, array(), array(), $orderBy, $limit, 0) . ") ";

        return $cte . self::update(SqlFragment::make("cte"), $values);
    }

    /**
     * Render selection (affection) limit and offset. Keywords for LIMIT and OFFSET will be included, attention, this
     * method will render limit and offset independently which may not be supported by some databases.
     *
     * @link http://stackoverflow.com/questions/2135418/equivalent-of-limit-and-offset-for-sql-server
     * @param int    $limit
     * @param int    $offset
     * @param string $rowNumber Name of row number column.
     * @return string
     */
    public function limit($limit, $offset, $rowNumber = null)
    {
        if (!$rowNumber && $this->driver->getServerVersion() >= 12)
        {
            $statement = "OFFSET {$offset} ROWS ";

            if ($limit)
            {
                $statement .= "FETCH NEXT {$limit} ROWS ONLY";
            }

            return trim($statement);
        }

        $statement = "WHERE {$this->quote($rowNumber)} ";

        //0 = row_number(1)
        $offset = $offset + 1;

        if ($limit)
        {
            $statement .= "BETWEEN {$offset} AND " . ($offset + $limit - 1);
        }
        else
        {
            $statement .= ">= {$offset}";
        }

        return $statement;
    }
}