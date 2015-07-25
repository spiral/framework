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
use Spiral\Core\Component\LoggerTrait;

class QueryCompiler extends BaseQueryCompiler
{
    /**
     * For warnings.
     */
    use LoggerTrait;

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
            list($table, $alias) = explode($matches[0], $table);
        }
        elseif (empty($joins))
        {
            return parent::delete($table, $joins, $where);
        }

        return self::delete($table) . "\nWHERE {$this->quote('rowid')} IN (\n"
        . self::select([$table . ' AS ' . $alias], false, [$alias . '.rowid'], $joins, $where)
        . "\n)";
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
        self::logger()->warning(
            "SQLite UPDATE statement are very limited, you can not use complex SET statements."
        );

        $alias = $table;
        if (preg_match('/ as /i', $alias, $matches))
        {
            list($table, $alias) = explode($matches[0], $table);
        }
        elseif (empty($joins))
        {
            return parent::update($table, $columns, $joins, $where);
        }

        return parent::update($table, $columns) . " WHERE {$this->quote('rowid')} IN (\n"
        . self::select([$table . ' AS ' . $alias], false, [$alias . '.rowid'], $joins, $where)
        . "\n)";
    }

    /**
     * Compile insert query statement. Table name (without prefix), columns and list of rowsets is
     * required.
     *
     * @param string $table   Table name without prefix.
     * @param array  $columns Columns name.
     * @param array  $rowsets List of rowsets, usually presented by Parameter instances as every rowset
     *                        is array of values.
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
                $selectColumns = [];
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
     * Render selection (affection) limit and offset. SQLite limit should be always provided (if
     * offset not empty).
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
            $statement = "LIMIT " . ($limit ?: '-1') . " ";
        }

        if ($offset)
        {
            $statement .= "OFFSET {$offset}";
        }

        return trim($statement);
    }
}