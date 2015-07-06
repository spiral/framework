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
     * Compile delete query statement. Table name, joins and where tokens are required.
     *
     * @param string $table
     * @param array  $joins
     * @param array  $where
     * @return string
     */
    public function delete($table, array $joins = [], array $where = [])
    {
        if (empty($joins))
        {
            return parent::delete($table, $joins, $where);
        }

        //Situation is little bit more complex when we have joins
        $statement = parent::delete($table);

        //We have to rebuild where tokens
        $whereTokens = [];

        //Converting JOINS into USING tables
        $usingTables = [];
        foreach ($joins as $table => $join)
        {
            $usingTables[] = $this->quote($table, true, true);
            $whereTokens = array_merge($whereTokens, $join['on']);
        }

        $statement .= "\nUSING " . join(', ', $usingTables);

        $whereTokens[] = ['AND', '('];
        $whereTokens = array_merge($whereTokens, $where);
        $whereTokens[] = ['', ')'];

        if (!empty($whereTokens))
        {
            $statement .= "\nWHERE " . $this->where($whereTokens);
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

        $statement = 'UPDATE ' . $this->quote($table, true, true);

        //We have to rebuild where tokens
        $whereTokens = [];

        //Converting JOINS into FROM tables
        $fromTables = [];
        foreach ($joins as $table => $join)
        {
            $fromTables[] = $this->quote($table, true, true);
            $whereTokens = array_merge($whereTokens, $join['on']);
        }

        $statement .= "\nSET" . $this->prepareColumns($columns);
        $statement .= "\nFROM " . join(', ', $fromTables);

        $whereTokens[] = ['AND', '('];
        $whereTokens = array_merge($whereTokens, $where);
        $whereTokens[] = ['', ')'];

        if (!empty($whereTokens))
        {
            $statement .= "\nWHERE " . $this->where($whereTokens);
        }

        return rtrim($statement);
    }

    /**
     * Compile insert query statement. Table name (without prefix), columns and list of rowsets is
     * required.
     *
     * @param string $table      Table name without prefix.
     * @param array  $columns    Columns name.
     * @param array  $rowsets    List of rowsets, usually presented by Parameter instances as every
     *                           rowset is array of values.
     * @param string $primaryKey Primary key name to return.
     * @return string
     * @throws DBALException
     */
    public function insert($table, array $columns, array $rowsets, $primaryKey = '')
    {
        return parent::insert($table, $columns, $rowsets)
        . (!empty($primaryKey) ? ' RETURNING ' . $this->quote($primaryKey) : '');
    }

    /**
     * Compile DISTINCT query statement chunk. Postgres supports distinct condition.
     *
     * @param mixed $distinct
     * @return string
     */
    protected function distinct($distinct)
    {
        return "DISTINCT" . (is_string($distinct) ? '(' . $this->quote($distinct) . ')' : '');
    }
}