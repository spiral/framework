<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Components\DBAL\Builders\SelectQuery;
use Spiral\Core\Component;

class QueryCompiler extends Component
{
    /**
     * Query types for parameter ordering.
     */
    const SELECT_QUERY = 'select';
    const UPDATE_QUERY = 'update';
    const DELETE_QUERY = 'delete';
    const INSERT_QUERY = 'insert';

    /**
     * Parent driver instance, driver used only for identifier() methods but can be required in other
     * cases.
     *
     * @var Driver
     */
    protected $driver = null;

    /**
     * Active table prefix. Table prefix defined on database level is will change every quoted table
     * or column name.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Set of table name aliases, such aliases will not be prefixed by driver. Method will generate
     * set of aliases automatically every time "AS" condition will be met.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * QueryCompiler is low level SQL compiler which used by different query builders to generate
     * statement based on provided tokens. Every builder will get it's own QueryCompiler at it has
     * some internal isolation features (such as query specific table aliases).
     *
     * @param Driver $driver      Parent driver instance.
     * @param string $tablePrefix Active table prefix (defined on database level).
     */
    public function __construct(Driver $driver, $tablePrefix = '')
    {
        $this->driver = $driver;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Reset QueryCompiler aliases cache.
     *
     * @return static
     */
    public function resetAliases()
    {
        $this->aliases = [];

        return $this;
    }

    /**
     * Quote database table or column keyword according to driver rules, method can automatically
     * detect table names, SQL functions and used aliases (via keywords AS), last argument can be used
     * to collect such aliases.
     *
     * @param string $identifier  Identifier can include simple column operations and functions,
     *                            having "." in it will automatically force table prefix to first value.
     * @param bool   $table       Set to true to let quote method know that identified is related to
     *                            table name.
     * @param bool   $forceTable  In some cases we have to force prefix.
     * @return mixed|string
     */
    public function quote($identifier, $table = false, $forceTable = false)
    {
        if ($identifier instanceof SqlFragmentInterface)
        {
            return $identifier->sqlStatement($this);
        }

        if (preg_match('/ as /i', $identifier, $matches))
        {
            list($identifier, $alias) = explode($matches[0], $identifier);

            /**
             * We can't do looped aliases, so let's force table prefix for identifier if we aliasing
             * table name at this moment.
             */
            $quoted = $this->quote($identifier, $table, $table)
                . $matches[0]
                . $this->driver->identifier($alias);

            if ($table && strpos($identifier, '.') === false)
            {
                //We have to apply operation post factum to prevent self aliasing (name AS name
                //when db has prefix, expected: prefix_name as name)
                $this->aliases[$alias] = $identifier;
            }

            return $quoted;
        }

        if (strpos($identifier, '(') || strpos($identifier, ' '))
        {
            return preg_replace_callback('/([a-z][0-9_a-z\.]*\(?)/i', function ($identifier) use (&$table)
            {
                $identifier = $identifier[1];
                if (substr($identifier, -1) == '(')
                {
                    //Function name
                    return $identifier;
                }

                if ($table)
                {
                    $table = false;

                    //Only first table has to be escaped
                    return $this->quote($identifier, true);
                }

                return $this->quote($identifier);
            }, $identifier);
        }

        if (strpos($identifier, '.') === false)
        {
            if (($table && !isset($this->aliases[$identifier])) || $forceTable)
            {
                if (!isset($this->aliases[$this->tablePrefix . $identifier]))
                {
                    $this->aliases[$this->tablePrefix . $identifier] = $identifier;
                }

                $identifier = $this->tablePrefix . $identifier;
            }

            return $this->driver->identifier($identifier);
        }

        $identifier = explode('.', $identifier);

        //Expecting first element be table name
        if (!isset($this->aliases[$identifier[0]]))
        {
            $identifier[0] = $this->tablePrefix . $identifier[0];
        }

        //No aliases can be collected there
        $identifier = array_map([$this->driver, 'identifier'], $identifier);

        return join('.', $identifier);
    }

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
        return array_merge($columns, $joins, $where, $having);
    }

    /**
     * Compile insert query statement. Table name (without prefix), columns and list of rowsets is
     * required.
     *
     * @param string $table   Table name without prefix.
     * @param array  $columns Columns name.
     * @param array  $rowsets List of rowsets, usually presented by Parameter instances as every
     *                        rowset is array of values.
     * @return string
     * @throws DBALException
     */
    public function insert($table, array $columns, array $rowsets)
    {
        if (!$columns)
        {
            throw new DBALException("Unable to build insert statement, columns must be set.");
        }

        if (!$rowsets)
        {
            throw new DBALException(
                "Unable to build insert statement, at least one value set must be provided."
            );
        }

        return "INSERT INTO {$this->quote($table, true)} ({$this->columns($columns)})\n"
        . "VALUES " . join(",\n", $rowsets);
    }

    /**
     * Compile select query statement. Table names, distinct flag, columns, joins, where tokens,
     * having tokens, group by tokens (yeah, it's very big list), order by tokens, limit, offset
     * values and unions are required. While compilation table aliases will be collected from join
     * and table parts, which will allow their usage in every condition even if tablePrefix not empty.
     *
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
    public function select(
        array $from,
        $distinct,
        array $columns,
        array $joins = [],
        array $where = [],
        array $having = [],
        array $groupBy = [],
        array $orderBy = [],
        $limit = 0,
        $offset = 0,
        array $unions = []
    )
    {
        //This statement parts should be processed first to define set of table and column aliases
        $from = $this->tables($from);
        $joins = $joins ? $this->joins($joins) . ' ' : '';

        $distinct = $distinct ? $this->distinct($distinct) . ' ' : '';
        $columns = $this->columns($columns);

        //Conditions
        $where = $where ? "\nWHERE " . $this->where($where) . ' ' : '';
        $having = $having ? "\nHAVING " . $this->where($having) . ' ' : '';

        //Sortings and grouping
        $groupBy = $groupBy ? $this->groupBy($groupBy) . ' ' : '';

        //Initial statement have predictable order
        $statement = rtrim("SELECT\n{$distinct}{$columns}"
                . "\nFROM {$from} {$joins}{$where}{$groupBy}{$having}") . ' ';

        if (empty($unions) && !empty($orderBy))
        {
            $statement .= $this->orderBy($orderBy) . ' ';
        }

        if (!empty($unions))
        {
            $statement .= $this->unions($unions) . ' ';
        }

        if (!empty($unions) && !empty($orderBy))
        {
            $statement .= $this->orderBy($orderBy) . ' ';
        }

        if ($limit || $offset)
        {
            $statement .= $this->limit($limit, $offset);
        }

        return rtrim($statement);
    }

    /**
     * Compile delete query statement. Table name, joins and where tokens are required. Joins not
     * supported by default.
     *
     * @param string $table
     * @param array  $joins
     * @param array  $where
     * @return string
     */
    public function delete($table, array $joins = [], array $where = [])
    {
        $statement = 'DELETE FROM ' . $this->quote($table, true);

        if (!empty($where))
        {
            $statement .= "\nWHERE " . $this->where($where);
        }

        return rtrim($statement);
    }

    /**
     * Compile update query statement. Table name, set of values (associated with column names), joins
     * and where tokens are required. Joins not supported by default.
     *
     * @param string $table
     * @param array  $columns
     * @param array  $joins
     * @param array  $where
     * @return string
     */
    public function update($table, array $columns, array $joins = [], array $where = [])
    {
        $statement = 'UPDATE ' . $this->quote($table, true, true) . "\n";
        $statement .= "SET" . $this->prepareColumns($columns);

        if (!empty($where))
        {
            $statement .= "\nWHERE " . $this->where($where);
        }

        return rtrim($statement);
    }

    /**
     * Prepare columns to be used in UPDATE statement.
     *
     * @param array  $columns
     * @param string $tableAlias Forced table alias for updated columns.
     * @return array
     */
    protected function prepareColumns(array $columns, $tableAlias = '')
    {
        foreach ($columns as $column => &$value)
        {
            if ($value instanceof SelectQuery)
            {
                $value = '(' . $value->sqlStatement($this) . ')';
            }
            elseif ($value instanceof SqlFragmentInterface)
            {
                $value = $value->sqlStatement($this);
            }
            else
            {
                $value = '?';
            }

            if (strpos($column, '.') === false && !empty($tableAlias))
            {
                $column = $tableAlias . '.' . $column;
            }

            $value = ' ' . $this->quote($column) . ' = ' . $value;

            unset($value);
        }

        return join(", ", $columns);
    }

    /**
     * Compile DISTINCT query statement chunk.
     *
     * @param mixed $distinct
     * @return string
     */
    protected function distinct($distinct)
    {
        return 'DISTINCT';
    }

    /**
     * Compile table names statement chunk, can work both for single table and list of names.
     *
     * @param array $tables
     * @return string
     */
    public function tables(array $tables)
    {
        foreach ($tables as &$table)
        {
            $table = $this->quote($table, true, true);
            unset($table);
        }

        return join(', ', $tables);
    }

    /**
     * Compile column names statement chunk.
     *
     * @param array $columns
     * @return string
     */
    public function columns(array $columns)
    {
        return wordwrap(join(', ', array_map([$this, 'quote'], $columns)), 180);
    }

    /**
     * Compile joins including their type and ON conditions. Keyword "JOIN" will be included.
     *
     * @param array $joins
     * @return string
     * @throws DBALException
     */
    public function joins(array $joins)
    {
        $statement = '';
        foreach ($joins as $table => $join)
        {
            $statement .= "\n" . $join['type'] . ' JOIN ' . $this->quote($table, true, true);

            if (!empty($join['on']))
            {
                $statement .= ' ON ' . $this->where($join['on']);
            }
        }

        return $statement;
    }

    /**
     * Compile where statement, WHERE keywords will not be included.
     *
     * @param array $tokens
     * @return string
     * @throws DBALException
     */
    public function where(array $tokens)
    {
        if (!$tokens)
        {
            return '';
        }

        $statement = '';

        $activeGroup = true;
        foreach ($tokens as $condition)
        {
            $joiner = $condition[0];
            $context = $condition[1];

            //First condition in group/query, no any AND, OR required
            if ($activeGroup)
            {
                //Kill AND, OR and etc.
                $joiner = '';

                //Next conditions require AND or OR
                $activeGroup = false;
            }
            else
            {
                $joiner .= ' ';
            }

            if ($context == '(')
            {
                //New where group.
                $activeGroup = true;
            }

            if (is_string($context))
            {
                $statement = rtrim($statement . $joiner)
                    . ($joiner && $context == '(' ? ' ' : '')
                    . $context
                    . ($context == ')' ? ' ' : '');

                continue;
            }

            if ($context instanceof SelectQuery)
            {
                $statement .= $joiner . ' (' . $context->sqlStatement($this) . ') ';
                continue;
            }

            if ($context instanceof SqlFragmentInterface)
            {
                //( ?? )
                $statement .= $joiner . ' ' . $context->sqlStatement($this) . ' ';
                continue;
            }

            list($identifier, $operator, $value) = $context;
            if ($identifier instanceof SqlFragmentInterface)
            {
                $identifier = '(' . $identifier->sqlStatement($this) . ')';
            }
            else
            {
                $identifier = $this->quote($identifier);
            }

            if ($operator == 'BETWEEN' || $operator == 'NOT BETWEEN')
            {
                $statement .= "{$joiner} {$identifier} " . "{$operator} "
                    . "{$this->renderValue($value)} AND {$this->renderValue($context[3])} ";

                continue;
            }

            if ($value === null || ($value instanceof ParameterInterface && $value->getValue() === null))
            {
                $operator = $operator == '=' ? 'IS' : 'IS NOT';
            }

            if (
                $operator == '='
                && (
                    is_array($value)
                    || ($value instanceof ParameterInterface && is_array($value->getValue()))
                )
            )
            {
                $operator = 'IN';
            }

            if ($value instanceof SelectQuery)
            {
                $value = ' (' . $value . ') ';
            }
            else
            {
                $value = $this->renderValue($value);
            }

            $statement .= "{$joiner}{$identifier} {$operator} {$value} ";
        }

        if ($activeGroup)
        {
            throw new DBALException("Unable to build where statement, unclosed where group.");
        }

        return trim($statement);
    }

    /**
     * Prepare value for inserting into query (replace ?).
     *
     * @param string $value
     * @return string
     */
    protected function renderValue($value)
    {
        if ($value instanceof SqlFragmentInterface)
        {
            return $value->sqlStatement($this);
        }

        return '?';
    }

    /**
     * Compile union statement chunk. Keywords UNION and ALL will be included, this methods will
     * automatically move every union on new line.
     *
     * @param array $unions
     * @return string
     */
    public function unions(array $unions)
    {
        $statement = '';
        foreach ($unions as $union)
        {
            $statement .= "\nUNION {$union[1]} \n({$union[0]})";
        }

        return $statement;
    }

    /**
     * Compile ORDER BY statement chunk, keyword "ORDER BY" will be included.
     *
     * @param array $orderBy
     * @return string
     */
    public function orderBy(array $orderBy)
    {
        $statement = 'ORDER BY ';

        foreach ($orderBy as $item)
        {
            $statement .= $this->quote($item[0]) . ' ' . strtoupper($item[1]);
        }

        return $statement;
    }

    /**
     * Compile GROUP BY statement chunk, keyword "GROUP BY" will be included.
     *
     * @param array $groupBy
     * @return string
     */
    public function groupBy(array $groupBy)
    {
        $statement = 'GROUP BY ';

        foreach ($groupBy as $identifier)
        {
            $statement .= $this->quote($identifier);
        }

        return $statement;
    }

    /**
     * Render selection (affection) limit and offset. Keywords for LIMIT and OFFSET will be included,
     * attention, this method will render limit and offset independently which may not be supported
     * by some databases.
     *
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset)
    {
        $statement = '';

        if (!empty($limit))
        {
            $statement = "LIMIT {$limit} ";
        }

        if (!empty($offset))
        {
            $statement .= "OFFSET {$offset}";
        }

        return trim($statement);
    }
}