<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\Builders\AbstractSelectQuery;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Components\ORM\Selector\Loader;
use Spiral\Components\ORM\Selector\Loaders\RootLoader;
use Spiral\Core\Component;
use Spiral\Facades\Cache;

class Selector extends AbstractSelectQuery
{
    const INLOAD   = 1;
    const POSTLOAD = 2;

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

    protected $countColumns = 0;

    protected $model = '';

    public function __construct(
        $model,
        ORM $orm,
        array $query = [],
        Loader $loader = null
    )
    {
        $this->model = $model;
        $this->orm = $orm;

        //Flushing columns
        $this->columns = [];

        //We aways need primary loader
        $this->loader = !empty($loader)
            ? $loader
            : new RootLoader($orm->getSchema($model), $this->orm);
    }

    /**
     * TODO: Write description
     *
     * @param string|array $relation
     * @param array        $options
     * @return static
     */
    public function with($relation, $options = [])
    {
        if (is_array($relation))
        {
            foreach ($relation as $name => $options)
            {
                //Multiple relations or relation with addition load options
                $this->with($name, $options);
            }

            return $this;
        }

        //TODO: Cross-db loaders

        //Nested loader
        $loader = $this->loader->addLoader($relation, $options);

        return $this;
    }

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
            //In cases where compiled does not provided externally we can get compiler from related
            //database, external compilers are good for testing
            $compiler = $this->loader->dbalDatabase()->getDriver()->queryCompiler(
                $this->loader->dbalDatabase()->getPrefix()
            );
        }

        $this->loader->clarifySelector($this);

        return $compiler->select(
            [$this->loader->getTable() . ' AS ' . $this->loader->getAlias()],
            false, //todo: check if required
            !empty($this->columns) ? $this->columns : ['*'],
            $this->joins,
            $this->whereTokens,
            $this->havingTokens,
            []
        //$this->limit,
        //$this->offset
        );
    }

    /**
     * Get interpolated (populated with parameters) SQL which will be run against database, please
     * use this method for debugging purposes only.
     *
     * @return string
     */
    public function queryString()
    {
        return DatabaseManager::interpolateQuery(
            $this->sqlStatement(),
            $this->loader->dbalDatabase()->getDriver()->prepareParameters($this->getParameters())
        );
    }

    /**
     * Fetch and normalize query data (will create nested models structure).
     *
     * @return array
     */
    public function fetchData()
    {
        $parameters = $this->parameters;
        $this->parameters = [];

        $statement = $this->sqlStatement();
        $this->parameters = array_merge($this->parameters, $parameters);

        if (!empty($this->lifetime))
        {
            $cacheIdentifier = md5(serialize([$statement, $this->getParameters()]));
            $cacheStore = $this->cacheStore ?: Cache::getInstance()->store();

            if ($cacheStore->has($cacheIdentifier))
            {
                //TODO: make it better
                $this->parameters = $parameters;

                //We are going to store parsed result, not queries
                return $cacheStore->get($cacheIdentifier);
            }
        }

        $result = $this->loader->dbalDatabase()->query($statement, $this->getParameters());

        //In many cases (too many inloads, too complex queries) parsing may take significant amount
        //of time, so we better profile it
        benchmark('selector::parseResult', $statement);
        $data = $this->loader->parseResult($result, $rowsCount);
        benchmark('selector::parseResult', $statement);

        //To let developer know that something bad about his query
        !empty($data) && $this->checkCounts(count($data), $rowsCount);

        //Moved out of benchmark to see memory usage
        $result->close();

        //Looking for post selectors
        foreach ($this->loader->getPostSelectors() as $selector)
        {
            //Fetching data from post selectors, due loaders are still linked together
            $selector->fetchData();
        }

        //We have to fetch result again after postloader were executed
        $data = $this->loader->getResult();
        $this->loader->clean();

        if (!empty($this->lifetime) && !empty($cacheStore) && !empty($cacheIdentifier))
        {
            $cacheStore->set($cacheIdentifier, $data, $this->lifetime);
        }

        $this->parameters = $parameters;

        return $data;
    }

    /**
     * Dedicating model creation based on fetched data to external class.
     *
     * @return ModelIterator
     */
    public function getIterator()
    {
        return new ModelIterator($this->model, $this->fetchData());
    }

    /**
     * Alias for getIterator() method.
     *
     * @return ModelIterator
     */
    public function all()
    {
        return $this->getIterator();
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
        $logLevel = 'debug';
        $logLevels = [
            1000 => 'critical',
            500  => 'alert',
            100  => 'notice',
            10   => 'warning',
            1    => 'debug'
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

        self::logger()->$logLevel(
            "Query resulted with {rowsCount} row(s) grouped into {dataCount} records.",
            compact('dataCount', 'rowsCount')
        );
    }

    /**
     * Generate set of columns required to represent desired model. Do not use this method by your
     * own. Method will return columns offset.
     *
     * @param string $tableAlias
     * @param array  $columns Original set of model columns.
     * @return int
     */
    public function registerColumns($tableAlias, array $columns)
    {
        $offset = count($this->columns);

        foreach ($columns as $column)
        {
            $this->columns[] = $tableAlias . '.' . $column . ' AS c' . (++$this->countColumns);
        }

        return $offset;
    }
}