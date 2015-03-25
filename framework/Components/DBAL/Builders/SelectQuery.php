<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders;

use Spiral\Components\DBAL\Builders\Common\HavingTrait;
use Spiral\Components\DBAL\Builders\Common\JoinTrait;
use Spiral\Components\DBAL\Builders\Common\WhereTrait;
use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Components\DBAL\QueryResult;
use Spiral\Components\DBAL\SqlFragmentInterface;
use Spiral\Support\Pagination\PaginableInterface;
use Spiral\Support\Pagination\PaginatorTrait;

class SelectQuery extends QueryBuilder implements
    PaginableInterface,
    \IteratorAggregate,
    \JsonSerializable
{
    /**
     * Select builder uses where, join traits and can be paginated.
     */
    use WhereTrait, JoinTrait, HavingTrait, PaginatorTrait;

    /**
     * Array of table names data should be fetched from. This list may include aliases (AS) construction,
     * system will automatically resolve them which allows fetching columns from multiples aliased
     * tables even if databases has table prefix.
     *
     * @var array
     */
    protected $fromTables = array();

    /**
     * Flag to indicate that query is distinct.
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * Columns or expressions to be fetched from database, can include aliases (AS).
     *
     * @var array
     */
    protected $columns = array('*');

    /**
     * Array of columns or/and expressions to be used to generate ORDER BY statement. Every orderBy
     * token should include correct identifier (or expression) and sorting direction (ASC, DESC).
     *
     * @var array
     */
    protected $orderBy = array();

    /**
     * Column names or expressions to group by.
     *
     * @var array
     */
    protected $groupBy = array();

    /**
     * Cache lifetime. Can be set at any moment and will change behaviour os run() method, if set -
     * query will be performed using Database->cached() function.
     *
     * @var int
     */
    protected $cache = 0;

    /**
     * List of QueryBuilder or SQLFragment object which should be joined to query using UNION or UNION
     * ALL syntax. If query is instance of SelectBuilder it'a all parameters will be automatically
     * merged with query parameters.
     *
     * @var array
     */
    protected $unions = array();

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
        array $from = array(),
        array $columns = array()
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
     * Set distinct flag to true/false. Applying distinct to select query will return only unique
     * records from database.
     *
     * @param bool $distinct
     * @return static
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;

        return $this;
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
     * Specify grouping identifier or expression for select query.
     *
     * @param string $identifier
     * @return static
     */
    public function groupBy($identifier)
    {
        $this->groupBy[] = $identifier;

        return $this;
    }

    /**
     * Add results ordering. Order should be specified by identifier or expression and sorting direction.
     * Multiple orderBy() methods can be applied to one query. In case of unions order by will be
     * applied to united result.
     *
     * @param string $identifier Column or expression of SqlFragment.
     * @param string $direction  Sorting direction, ASC|DESC.
     * @return static
     */
    public function orderBy($identifier, $direction = 'ASC')
    {
        $this->orderBy[] = array($identifier, $direction);

        return $this;
    }

    /**
     * Combine result with external select query, parameters will be merged in resulted statement.
     * Only distinct results will be included. You can specify query as plain statement
     * (SqlFragmentInterface) or using select query builder.
     *
     * @param SqlFragmentInterface|SelectQuery $query
     * @return static
     */
    public function union(SqlFragmentInterface $query)
    {
        $this->unions[] = array($query, '');

        return $this;
    }

    /**
     * Combine result with external select query, parameters will be merged in resulted statement.
     * All (even distinct) result will be included. You can specify query as plain statement
     * (SqlFragmentInterface) or using select query builder.
     *
     * @param SqlFragmentInterface|SelectQuery $query
     * @return static
     */
    public function unionAll(SqlFragmentInterface $query)
    {
        $this->unions[] = array($query, 'ALL');

        return $this;
    }

    /**
     * Specify that query result should be cached for specified amount of seconds. Attention, this
     * method will apply caching to every result generated by SelectBuilder including count() and
     * aggregation methods().
     *
     * @param int $lifetime Cache lifetime in seconds.
     * @return static
     */
    public function cache($lifetime)
    {
        $this->cache = $lifetime;

        return $this;
    }

    /**
     * Get query binder parameters. Method can be overloaded to perform some parameters manipulations.
     * SelectBuilder will merge it's own parameters with parameters defined in UNION queries.
     *
     * @return array
     */
    public function getParameters()
    {
        $parameters = $this->parameters;
        foreach ($this->unions as $union)
        {
            if ($union[0] instanceof QueryBuilder)
            {
                $parameters = array_merge($parameters, $union[0]->getParameters());
            }
        }

        return $parameters;
    }

    /**
     * Get or render SQL statement.
     *
     * @return string
     */
    public function sqlStatement()
    {
        return $this->compiler->select(
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

    /**
     * Run QueryBuilder statement against parent database. Method will be overloaded by child builder
     * to return correct value.
     *
     * @param bool $paginate True is pagination should be applied.
     * @return QueryResult
     */
    public function run($paginate = true)
    {
        $paginate && $this->doPagination();

        if (!empty($this->cache))
        {
            return $this->database->cached($this->cache, $this->sqlStatement(), $this->getParameters());
        }

        return $this->database->query($this->sqlStatement(), $this->getParameters());
    }

    /**
     * Counts the number of results for this query. Limit and offset values will be ignored. Attention,
     * method results will be cached (if requested), which means that attached paginator can work
     * incorrectly. Attention, you can't really use count() methods with united queries (at least
     * without tweaking every united query).
     *
     * @return int
     */
    public function count()
    {
        $backup = array($this->columns, $this->orderBy, $this->groupBy, $this->limit, $this->offset);
        $this->columns = array('COUNT(*)');

        //Can not be used with COUNT()
        $this->orderBy = $this->groupBy = array();
        $this->limit = $this->offset = 0;

        $result = $this->run(false)->fetchColumn();
        list($this->columns, $this->orderBy, $this->groupBy, $this->limit, $this->offset) = $backup;

        return (int)$result;
    }

    /**
     * Perform one of SELECT aggregation methods. Supported methods: AVG, MIN, MAX, SUM. Attention,
     * you can't use aggregation methods with united queries without explicitly specifying aggregation
     * as column in every nested query.
     *
     * @param string $method
     * @param array  $arguments
     * @return int
     * @throws DBALException
     */
    public function __call($method, $arguments)
    {
        $columns = $this->columns;

        if (!in_array($method = strtoupper($method), array('AVG', 'MIN', 'MAX', 'SUM')))
        {
            throw new DBALException("Unknown aggregation method '{$method}'.");
        }

        $this->columns = array("{$method}(" . join(", ", $arguments) . ")");

        $result = $this->run(false)->fetchColumn();
        $this->columns = $columns;

        return (int)$result;
    }

    /**
     * Retrieve an external iterator, SelectBuilder will return QueryResult as iterator.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return QueryResult
     */
    public function getIterator()
    {
        return $this->run();
    }

    /**
     * (PHP 5 > 5.4.0)
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->run()->jsonSerialize();
    }
}