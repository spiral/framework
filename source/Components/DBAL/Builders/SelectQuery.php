<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders;

use Spiral\Components\DBAL\Builders\Common\AbstractSelectQuery;
use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;

class SelectQuery extends AbstractSelectQuery
{
    /**
     * Array of table names data should be fetched from. This list may include aliases (AS) construction,
     * system will automatically resolve them which allows fetching columns from multiples aliased
     * tables even if databases has table prefix.
     *
     * @var array
     */
    protected $fromTables = [];

    /**
     * List of QueryBuilder or SQLFragment object which should be joined to query using UNION or UNION
     * ALL syntax. If query is instance of SelectBuilder it'a all parameters will be automatically
     * merged with query parameters.
     *
     * @var array
     */
    protected $unions = [];

    /**
     * SelectBuilder used to generate SELECT query statements, it can as to directly fetch data from
     * database or as nested select query in other builders.
     *
     * @param Database      $database Parent database.
     * @param QueryCompiler $compiler Driver specific QueryGrammar instance (one per builder).
     * @param array         $from     Initial set of table names.
     * @param array         $columns  Initial set of columns to fetch.
     */
    public function __construct(
        Database $database,
        QueryCompiler $compiler,
        array $from = [],
        array $columns = []
    )
    {
        parent::__construct($database, $compiler);

        $this->fromTables = $from;
        if ($columns)
        {
            $this->columns = $this->fetchIdentifiers($columns);
        }
    }

    /**
     * Set columns should be fetched as result of SELECT query. Columns can be provided with specified
     * alias (AS construction).
     *
     * @param array|string|mixed $columns Array of names, comma separated string or set of parameters.
     * @return static
     */
    public function columns($columns)
    {
        $this->columns = $this->fetchIdentifiers(func_get_args());

        return $this;
    }

    /**
     * Alias for columns() method. Set columns should be fetched as result of SELECT query. Columns
     * can be provided with specified alias (AS construction).
     *
     * @param array|string|mixed $columns Array of names, comma separated string or set of parameters.
     * @return static
     */
    public function select($columns)
    {
        $this->columns = $this->fetchIdentifiers(func_get_args());

        return $this;
    }

    /**
     * Set table names SELECT query should be performed for. Table names can be provided with specified
     * alias (AS construction).
     *
     * @param array|string|mixed $tables Array of names, comma separated string or set of parameters.
     * @return static
     */
    public function from($tables)
    {
        $this->fromTables = $this->fetchIdentifiers(func_get_args());

        return $this;
    }

    /**
     * Get ordered list of builder parameters.
     *
     * @param QueryCompiler $compiler
     * @return array
     */
    public function getParameters(QueryCompiler $compiler = null)
    {
        $parameters = parent::getParameters(
            $compiler = !empty($compiler) ? $compiler : $this->compiler
        );

        //Unions always located at the end of query.
        foreach ($this->unions as $union)
        {
            if ($union[0] instanceof QueryBuilder)
            {
                $parameters = array_merge($parameters, $union[0]->getParameters($compiler));
            }
        }

        return $parameters;
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        $compiler = !empty($compiler) ? $compiler : $this->compiler->resetAliases();

        return $compiler->select(
            $this->fromTables,
            $this->distinct,
            $this->columns,
            $this->joins,
            $this->whereTokens,
            $this->havingTokens,
            $this->groupBy,
            $this->orderBy,
            $this->limit,
            $this->offset,
            $this->unions
        );
    }
}