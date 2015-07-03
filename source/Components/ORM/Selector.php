<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Psr\Log\LogLevel;
use Spiral\Components\DBAL\Builders\Common\AbstractSelectQuery;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Components\DBAL\QueryResult;
use Spiral\Components\ORM\Selector\Loader;
use Spiral\Components\ORM\Selector\Loaders\RootLoader;
use Spiral\Core\Component;
use Spiral\Facades\Cache;

class Selector extends AbstractSelectQuery
{
    /**
     * To warn user about non optimal queries.
     */
    use Component\LoggerTrait;

    const INLOAD   = 1;
    const POSTLOAD = 2;

    /**
     * Class name used to define schema. Following class name will be used in ModelIterator to
     * create entities based on selected data.
     *
     * @var string
     */
    protected $class = '';

    /**
     * ORM component. Used to access related schemas.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    /**
     * REWRITE
     *
     * @var Loader
     */
    protected $loader = null;

    /**
     * Set of columns defined by loaders. Every loader column has defined alias used to present
     * collisions between column names in different tables, such columns will be used only in case
     * if no user specified columns provided.
     *
     * @var array
     */
    protected $registeredColumns = [];

    /**
     * We have to track count of loader columns to define correct offsets.
     *
     * @var int
     */
    protected $countColumns = 0;

    /**
     * Selector provides SelectQuery like functionality to fetch data from model related table
     * and database.
     *
     * @param \Spiral\Components\DBAL\Database $class
     * @param ORM                              $orm
     * @param Loader                           $loader
     */
    public function __construct($class, ORM $orm, Loader $loader = null)
    {
        $this->class = $class;
        $this->orm = $orm;

        //Flushing columns
        $this->columns = $this->registeredColumns = [];

        //We aways need primary loader
        if (empty($this->loader = $loader))
        {
            $this->loader = new RootLoader($class, $this->orm);
        }

        $database = $this->loader->dbalDatabase();
        parent::__construct(
            $database,
            $database->getDriver()->queryCompiler($database->getPrefix())
        );
    }

    /**
     * TODO: Write description
     *
     * @param string|array $relation
     * @param array        $options
     * @return static
     */
    //    public function with($relation, $options = [])
    //    {
    //        if (is_array($relation))
    //        {
    //            foreach ($relation as $name => $options)
    //            {
    //                //Multiple relations or relation with addition load options
    //                $this->with($name, $options);
    //            }
    //
    //            return $this;
    //        }
    //
    //        //TODO: Cross-db loaders
    //
    //        //Nested loader
    //        $loader = $this->loader->addLoader($relation, $options);
    //
    //        return $this;
    //    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        if (empty($compiler))
        {
            //We have to reset aliases if we own this compiler
            $compiler = $this->compiler->resetAliases();
        }

        //Primary loader may add custom conditions to select query
        $this->loader->clarifySelector($this);

        return $compiler->select(
            [$this->loader->getTable() . ' AS ' . $this->loader->getAlias()],
            $this->distinct,
            $this->getColumns(),
            $this->joins,
            $this->whereTokens,
            $this->havingTokens,
            $this->groupBy,
            $this->orderBy,
            $this->limit,
            $this->offset
        );
    }

    /**
     * We have to redefine selector iterator and result or selection is set of models not columns.
     *
     * @return ModelIterator
     */
    public function getIterator()
    {
        return new ModelIterator($this->class, $this->fetchData());
    }

    /**
     * Set columns should be fetched as result of SELECT query. Columns can be provided with specified
     * alias (AS construction). QueryResult will be returned as result. No post loaders will be
     * executed.
     *
     * @param array|string|mixed $columns Array of names, comma separated string or set of parameters.
     * @return QueryResult
     */
    public function fetchColumns($columns = ['*'])
    {
        $this->columns = $this->fetchIdentifiers(func_get_args());

        return $this->run();
    }

    /**
     * Fetch and normalize query data (will create nested models structure). This method used to
     * build models tree and applies caching on much higher level.
     *
     * @return array
     */
    public function fetchData()
    {
        $statement = $this->sqlStatement();

        if (!empty($this->lifetime))
        {
            $cacheIdentifier = md5(serialize([$statement, $this->getParameters()]));
            $cacheStore = $this->cacheStore ?: Cache::getInstance()->store();

            if ($cacheStore->has($cacheIdentifier))
            {
                //We are going to store parsed result, not queries
                return $cacheStore->get($cacheIdentifier);
            }
        }

        //We are bypassing run() method here to prevent query caching, we will prefer to cache
        //parsed data rather that database response
        $result = $this->database->query(
            $statement,
            $this->getParameters()
        );

        //In many cases (too many inloads, too complex queries) parsing may take significant amount
        //of time, so we better profile it
        benchmark('selector::parseResult', $statement);
        $data = $this->loader->parseResult($result, $rowsCount);
        benchmark('selector::parseResult', $statement);

        //To let developer know that something bad about his query
        !empty($data) && $this->checkCounts(count($data), $rowsCount);

        //Moved out of benchmark to see memory usage
        $result->close();

        //Looking for post selectors (external queries used to compile valid data set)
        foreach ($this->loader->getPostSelectors() as $selector)
        {
            //Fetching data from post selectors, due loaders are still linked together
            $selector->fetchData();
        }

        //We have to fetch result again after post-loader were executed
        $data = $this->loader->getResult();
        $this->loader->clean();

        if (!empty($this->lifetime) && !empty($cacheStore) && !empty($cacheIdentifier))
        {
            $cacheStore->set($cacheIdentifier, $data, $this->lifetime);
        }

        return $data;
    }



    //TODO: INLOAD
    //TODO: POSTLOAD

    //TODO: UPDATE
    //TODO: BLA BLA

    /**
     * Selector query columns can be specified multiple ways:
     * 1) Using registered columns (provided by loaders)
     * 2) Using user specified columns
     * 3) Automatically using aggregation
     *
     * @return array
     */
    protected function getColumns()
    {
        if (!empty($this->columns))
        {
            return $this->columns;
        }

        if (!empty($this->registeredColumns))
        {
            return $this->registeredColumns;
        }

        return ['*'];
    }

    /**
     * Generate set of columns required to represent desired model and it's relations. Do not use
     * this method by your own. Method will return columns offset.
     *
     * @param Loader $loader  Loader which requested columns to be added. We only need loader table
     *                        alias, but we want full class to make sure no-one will use this method
     *                        when they don't need to.
     * @param array  $columns Original set of model columns.
     * @return int
     */
    public function registerColumns(Loader $loader, array $columns)
    {
        $offset = count($this->registeredColumns);
        foreach ($columns as $column)
        {
            $columnAlias = 'c' . (++$this->countColumns);
            $this->registeredColumns[] = $loader->getAlias() . '.' . $column . ' AS ' . $columnAlias;
        }

        return $offset;
    }

    /**
     * Helper method used to verify that spiral performed optimal processing on fetched result set.
     * If query is too complex or has a lot of inload queries system may spend much more time building
     * valid data tree.
     *
     * @param int $dataCount
     * @param int $rowsCount
     */
    protected function checkCounts($dataCount, $rowsCount)
    {
        $logLevel = LogLevel::DEBUG;
        $logLevels = [
            1000 => LogLevel::CRITICAL,
            500  => LogLevel::ALERT,
            100  => LogLevel::NOTICE,
            10   => LogLevel::WARNING,
            1    => LogLevel::DEBUG
        ];

        $dataRatio = $rowsCount / $dataCount;
        if ($dataRatio == 1)
        {
            //No need to log it, everything seems fine
            return;
        }

        foreach ($logLevels as $ratio => $logLevel)
        {
            if ($dataRatio > $ratio)
            {
                break;
            }
        }

        self::logger()->log(
            $logLevel,
            "Query resulted with {rowsCount} row(s) grouped into {dataCount} records.",
            compact('dataCount', 'rowsCount')
        );
    }
}