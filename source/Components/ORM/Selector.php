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
use Spiral\Components\Cache\CacheManager;
use Spiral\Components\DBAL\Builders\Common\AbstractSelectQuery;
use Spiral\Components\DBAL\ParameterInterface;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Components\DBAL\QueryResult;
use Spiral\Components\DBAL\SqlFragmentInterface;
use Spiral\Components\ORM\Selector\Loader;
use Spiral\Components\ORM\Selector\Loaders\RootLoader;
use Spiral\Core\Component;

/**
 * @method static include ($relation, array $options = []) Alias for inload() method.
 */
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
     * User::find()->load('profile')->where('profile.value', $value);
     *
     * //Table "profiles" will be joined to query under "my_alias" alias
     * User::find()->load('profile', ['alias' => 'my_alias'])->where('my_alias.value', $value);
     *
     * //Table "statistics" will be joined to query under "profile_statistics" alias
     * User::find()->load('profile.statistics');
     *
     * //Table "statistics" will be joined to query under "stats" alias
     * User::find()->load('profile.statistics', ['alias' => 'stats']);
     *
     * Attention, in some cases you can't use aliases in where condition as system may include
     * relation data using external query, use "inload" or "quickJoin" methods to ensure that related
     * table is joined into query.
     *
     * @see inload()
     * @see postload()
     * @see with()
     * @param string   $relation    Relation name, or chain of relations separated by .
     * @param array    $options     Loader options (will be applied to last chain loader only).
     * @param int|null $chainMethod INLOAD, POSTLOAD, JOIN_ONLY method forced for all loaders in this
     *                              chain.
     * @return static
     */
    public function load($relation, array $options = [], $chainMethod = null)
    {
        if (is_array($relation))
        {
            foreach ($relation as $name => $options)
            {
                if (is_string($options))
                {
                    //Array of relation names
                    $this->load($options, [], $chainMethod);
                }
                else
                {
                    //Multiple relations or relation with addition load options
                    $this->load($name, $options, $chainMethod);
                }
            }

            return $this;
        }

        //Nested loader
        $this->loader->loader($relation, $options, $chainMethod);

        return $this;
    }

    /**
     * Pre-load model relations using table joining. If you don't need loaded data and using it
     * for WHERE statement only - use with() method.
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
     * Attention, you will not be able to paginate results if you joined HAS_MANY or MANY_TO_MANY
     * relation!
     *
     * Method has magic alias "include".
     *
     * @see load()
     * @see postload()
     * @see with()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function inload($relation, array $options = [])
    {
        return $this->load($relation, $options, self::INLOAD);
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
     * Method is not identical to join(), as it will configure all conditions automatically.
     *
     * Attention, you will not be able to paginate results if you joined HAS_MANY or MANY_TO_MANY
     * relation!
     *
     * @see load()
     * @see inload()
     * @see postload()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function with($relation, array $options = [])
    {
        return $this->load($relation, $options, self::JOIN_ONLY);
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
     * @see load()
     * @see inload()
     * @see with()
     * @param string $relation Relation name, or chain of relations separated by .
     * @param array  $options  Loader options (will be applied to last chain loader only).
     * @return static
     */
    public function postload($relation, array $options = [])
    {
        return $this->load($relation, $options, self::POSTLOAD);
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        //We have to reset aliases if we own this compiler
        $compiler = !empty($compiler) ? $compiler : $this->compiler->resetAliases();

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
     * Add where condition to statement (alias for where method). Where condition will be specified
     * with AND boolean joiner. Method supports nested queries and array based (mongo like) where
     * conditions. Every provided parameter will be automatically escaped in generated query.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $selector->find(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $selector->find('column', $value);
     * $selector->find('column', array(1, 2, 3));
     * $selector->find('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $selector->find('column', '=', $value);
     * $selector->find('column', 'IN', array(1, 2, 3));
     * $selector->find('column', 'LIKE', $string);
     * $selector->find('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $selector->find('column', 'between', 1, 10);
     * $selector->find('column', 'not between', 1, 10);
     * $selector->find('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $selector->find(function(Selector $select){
     *      $selector->find("name", "Wolfy-J")->orWhere("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $selector->find(["column" => 1]);
     * $selector->find(["column" => [">" => 1, "<" => 10]]);
     * $selector->find([
     *      "@or" => [
     *          ["id" => 1],
     *          ["column" => ["like" => "name"]]
     *      ]
     * ]);
     * $selector->find([
     *      '@or' => [
     *          ["id" => 1],
     *          ["id" => 2],
     *          ["id" => 3],
     *          ["id" => 4],
     *          ["id" => 5],
     *      ],
     *      "column" => [
     *          "like" => "name"
     *      ],
     *      'x'      => [
     *          '>' => 1,
     *          '<' => 10
     *      ]
     * ]);
     *
     * You can read more about complex where statements in official documentation or look mongo
     * queries examples.
     *
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     */
    public function find($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        return call_user_func_array([
            $this, 'where'
        ], func_get_args());
    }

    /**
     * Fetch one model from database. Attention, LIMIT statement will be used, meaning you can't
     * join HAS_MANY or MANY_TO_MANY relations using INLOAD (inload) or JOIN_ONLY (with) methods.
     *
     * @param array $where Selection WHERE statement.
     * @return ActiveRecord|null
     */
    public function findOne(array $where = [])
    {
        if (!empty($where))
        {
            $this->where($where);
        }

        $data = $this->limit(1)->fetchData();
        if (empty($data))
        {
            return null;
        }

        $class = $this->class;

        return new $class($data[0], true, $this->orm);
    }

    /**
     * Fetch one model from database using it's primary key. You can use INLOAD and JOIN_ONLY loaders
     * with HAS_MANY or MANY_TO_MANY relations with this method as no limit used.
     *
     * @param mixed $id Primary key value.
     * @return ActiveRecord|null
     * @throws ORMException
     */
    public function findByID($id)
    {
        $primaryKey = $this->loader->getPrimaryKey();
        if (empty($primaryKey))
        {
            throw new ORMException("Unable to fetch data by primary key, no primary key found.");
        }

        $data = $this->where($primaryKey, $id)->fetchData();
        if (empty($data))
        {
            return null;
        }

        $class = $this->class;

        return new $class($data[0], true, $this->orm);
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

        if (!empty($this->cacheLifetime))
        {
            $cacheKey = $this->cacheKey ?: md5(serialize([$statement, $this->getParameters()]));
            $cacheStore = $this->cacheStore ?: CacheManager::getInstance()->store();

            if ($cacheStore->has($cacheKey))
            {
                self::logger()->debug("Selector result fetched from cache.");

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

        if (!empty($this->cacheLifetime) && !empty($cacheStore) && !empty($cacheKey))
        {
            $cacheStore->set($cacheKey, $data, $this->cacheLifetime);
        }

        return $data;
    }

    /**
     * Update all matched records with provided columns set.
     *
     * @param array $columns
     * @return int
     */
    public function update(array $columns)
    {
        if (!empty($this->havingTokens))
        {
            throw new ORMException("Unable to build udpate statement with non empty having tokens.");
        }

        $statement = $this->updateStatement($columns);

        $normalized = [];
        foreach ($columns as $value)
        {
            if ($value instanceof QueryBuilder)
            {
                foreach ($value->getParameters() as $parameter)
                {
                    $normalized[] = $parameter;
                }

                continue;
            }

            if ($value instanceof SqlFragmentInterface && !$value instanceof ParameterInterface)
            {
                continue;
            }

            $normalized[] = $value;
        }

        return $this->database->execute($statement, $this->compiler->prepareParameters(
            QueryCompiler::UPDATE_QUERY,
            $this->whereParameters,
            $this->onParameters,
            [],
            $normalized
        ));
    }

    /**
     * Get update statement based on specified relations and conditions.
     *
     * @param array         $columns
     * @param QueryCompiler $compiler
     * @return string
     *
     */
    protected function updateStatement(array $columns, QueryCompiler $compiler = null)
    {
        $compiler = !empty($compiler) ? $compiler : $this->compiler->resetAliases();
        $this->loader->configureSelector($this);

        return $compiler->update(
            $this->loader->getTable() . ' AS ' . $this->loader->getAlias(),
            $columns,
            $this->joins,
            $this->whereTokens
        );
    }

    /**
     * Delete all matched records and return count of affected rows.
     *
     * @return int
     * @throws ORMException
     */
    public function delete()
    {
        if (!empty($this->havingTokens))
        {
            throw new ORMException("Unable to build delete statement with non empty having tokens.");
        }

        return $this->database->execute($this->deleteStatement(), $this->compiler->prepareParameters(
            QueryCompiler::DELETE_QUERY,
            $this->whereParameters,
            $this->onParameters
        ));
    }

    /**
     * Get delete statement based on specified relations and conditions.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    protected function deleteStatement(QueryCompiler $compiler = null)
    {
        $compiler = !empty($compiler) ? $compiler : $this->compiler->resetAliases();
        $this->loader->configureSelector($this);

        return $compiler->delete(
            $this->loader->getTable() . ' AS ' . $this->loader->getAlias(),
            $this->joins,
            $this->whereTokens
        );
    }

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

    /**
     * Magic methods to call aggregation methods or magic selector methods (include).
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ($method == 'include')
        {
            //This can be changed in future to look better
            return call_user_func_array([$this, 'inload'], $arguments);
        }

        return parent::__call($method, $arguments);
    }
}