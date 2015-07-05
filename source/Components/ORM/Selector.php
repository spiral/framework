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

    /**
     *
     */
    const INLOAD    = 1;
    const POSTLOAD  = 2;
    const JOIN_ONLY = 3;

    /**
     * Relation between count records / count rows and type of log message to be raised. Log message
     * will be raised only if amount of loaded rows higher than count records in normalized data tree.
     *
     * @var array
     */
    protected $logLevels = [
        1000 => LogLevel::CRITICAL,
        500  => LogLevel::ALERT,
        100  => LogLevel::NOTICE,
        10   => LogLevel::WARNING,
        1    => LogLevel::DEBUG,
        0    => LogLevel::DEBUG
    ];

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
     * Loader is responsible for configuring selection query, pre-loading inner relations and
     * mapping data.
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
            $this->loader = new RootLoader($this->orm, null, $this->orm->getSchema($class));
        }

        $database = $this->loader->dbalDatabase();
        parent::__construct(
            $database,
            $database->getDriver()->queryCompiler($database->getPrefix())
        );
    }

    /**
     * Pre-load model relations. System will pick most optimal way of data pre-loading based on
     * relation (INLOAD - related tables will be joined to query, POSTLOAD - related data will
     * be loaded using separate query).
     *
     * Use options to specify custom settings for relation loading.
     * You can request to pre-load one relation or chain of relations, in this case options will
     * be applied to last relation in chain.
     *
     * Joined tables will automatically receive alias which is identical to their relation name,
     * sub relations will be separated by _. You can change such alias using "alias" index of
     * options array.
     *
     * Examples:
     * //Table "profiles" will be joined to query under "profile" alias
     * User::find()->with('profile')->where('profile.value', $value);
     *
     * //Table "profiles" will be joined to query under "my_alias" alias
     * User::find()->with('profile', ['alias' => 'my_alias'])->where('my_alias.value', $value);
     *
     * //Table "statistics" will be joined to query under "profile_statistics" alias
     * User::find()->with('profile.statistics');
     *
     * //Table "statistics" will be joined to query under "stats" alias
     * User::find()->with('profile.statistics', ['alias' => 'stats']);
     *
     * Attention, in some cases you can't use aliases in where condition as system may include
     * relation data using external query, use "inload" or "quickJoin" methods to ensure that related
     * table is joined into query.
     *
     * @see inload()
     * @see postload()
     * @see quickJoin()
     * @param string   $relation    Relation name, or chain of relations separated by .
     * @param array    $options     Loader options (will be applied to last chain loader only).
     * @param int|null $chainMethod INLOAD, POSTLOAD, JOIN_ONLY method forced for all loaders in this
     *                              chain.
     * @return static
     */
    public function with($relation, array $options = [], $chainMethod = null)
    {
        if (is_array($relation))
        {
            foreach ($relation as $name => $options)
            {
                //Multiple relations or relation with addition load options
                $this->with($name, $options, $chainMethod);
            }

            return $this;
        }

        //Nested loader
        $this->loader->loader($relation, $options, $chainMethod);

        return $this;
    }

    /**
     * Pre-load model relations using table joining.
     *
     * Use options to specify custom settings for relation loading.
     * You can request to pre-load one relation or chain of relations, in this case options will
     * be applied to last relation in chain.
     *
     * Joined tables will automatically receive alias which is identical to their relation name,
     * sub relations will be separated by _. You can change such alias using "alias" index of
     * options array.
     *
     * Examples:
     * //Table "profiles" will be joined to query under "profile" alias
     * User::find()->inload('profile')->where('profile.value', $value);
     *
     * //Table "profiles" will be joined to query under "my_alias" alias
     * User::find()->inload('profile', ['alias' => 'my_alias'])->where('my_alias.value', $value);
     *
     * //Table "statistics" will be joined to query under "profile_statistics" alias
     * User::find()->inload('profile.statistics');
     *
     * //Table "statistics" will be joined to query under "stats" alias
     * User::find()->inload('profile.statistics', ['alias' => 'stats']);
     *
     * @see with()
     * @see postload()
     * @see quickJoin()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function inload($relation, array $options = [])
    {
        return $this->with($relation, $options, self::INLOAD);
    }

    /**
     * Include model relations data into query using table joining but do not load resulted data.
     * This method usually used in combination with WHERE statements.
     *
     * Use options to specify custom settings for relation loading.
     * You can request to pre-load one relation or chain of relations, in this case options will
     * be applied to last relation in chain.
     *
     * Joined tables will automatically receive alias which is identical to their relation name,
     * sub relations will be separated by _. You can change such alias using "alias" index of
     * options array.
     *
     * Examples:
     * //Table "profiles" will be joined to query under "profile" alias
     * User::find()->quickJoin('profile')->where('profile.value', $value);
     *
     * //Table "profiles" will be joined to query under "my_alias" alias
     * User::find()->quickJoin('profile', ['alias' => 'my_alias'])->where('my_alias.value', $value);
     *
     * //Table "statistics" will be joined to query under "profile_statistics" alias
     * User::find()->quickJoin('profile.statistics');
     *
     * //Table "statistics" will be joined to query under "stats" alias
     * User::find()->quickJoin('profile.statistics', ['alias' => 'stats']);
     *
     * Method is not identical to join(), as it will configure all conditions automatically.
     *
     * @see with()
     * @see postload()
     * @see quickJoin()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function quickJoin($relation, array $options = [])
    {
        return $this->with($relation, $options, self::JOIN_ONLY);
    }

    /**
     * Pre-load model relations using separate queries.
     *
     * Use options to specify custom settings for relation loading.
     * You can request to pre-load one relation or chain of relations, in this case options will
     * be applied to last relation in chain.
     *
     * Examples:
     * User::find()->postload('posts.comments');
     *
     * Following construction will create 3 separate query:
     * 1) Get current model data.
     * 2) Load posts
     * 3) Load comments
     *
     * Example SQL (simplified):
     * SELECT * FROM users;
     * SELECT * FROM posts WHERE user_id IN(user_ids);
     * SELECT * FROM comments WHERE post_id IN(post_ids);
     *
     * Attention, you will not be able to create WHERE statement for relations loaded using POSTLOAD
     * method.
     *
     * @see with()
     * @see inload()
     * @see quickJoin()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function postload($relation, array $options = [])
    {
        return $this->with($relation, $options, self::POSTLOAD);
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
            //We have to reset aliases if we own this compiler
            $compiler = $this->compiler->resetAliases();
        }

        //Primary loader may add custom conditions to select query
        $this->loader->configureSelector($this);

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
        return new ModelIterator($this->orm, $this->class, $this->fetchData());
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
            $cacheKey = $this->cacheKey ?: md5(serialize([$statement, $this->getParameters()]));
            $cacheStore = $this->cacheStore ?: Cache::getInstance()->store();

            if ($cacheStore->has($cacheKey))
            {
                //We are going to store parsed result, not queries
                return $cacheStore->get($cacheKey);
            }
        }

        //We are bypassing run() method here to prevent query caching, we will prefer to cache
        //parsed data rather that database response
        $result = $this->database->query($statement, $this->getParameters());

        //In many cases (too many inloads, too complex queries) parsing may take significant amount
        //of time, so we better profile it
        benchmark('selector::parseResult', $statement);
        $data = $this->loader->parseResult($result, $rowsCount);
        benchmark('selector::parseResult', $statement);

        //To let developer know that something bad about his query
        !empty($data) && $this->checkCounts(count($data), $rowsCount);

        //Moved out of benchmark to see memory usage
        $result->close();

        //Executing post-loading
        $this->loader->postLoad();

        //We have to fetch result again after post-loader were executed
        $data = $this->loader->getResult();
        $this->loader->clean();

        if (!empty($this->lifetime) && !empty($cacheStore) && !empty($cacheKey))
        {
            $cacheStore->set($cacheKey, $data, $this->lifetime);
        }

        return $data;
    }

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
     * @param string $table Source table name.
     * @param array  $columns Original set of model columns.
     * @return int
     */
    public function registerColumns($table, array $columns)
    {
        $offset = count($this->registeredColumns);
        foreach ($columns as $column)
        {
            $columnAlias = 'c' . (++$this->countColumns);
            $this->registeredColumns[] = $table . '.' . $column . ' AS ' . $columnAlias;
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
        $dataRatio = $rowsCount / $dataCount;
        if ($dataRatio == 1)
        {
            //No need to log it, everything seems fine
            return;
        }

        $logLevel = $this->logLevels[0];
        foreach ($this->logLevels as $ratio => $logLevel)
        {
            if ($dataRatio >= $ratio)
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