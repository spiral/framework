<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Components\Cache\CacheException;
use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\StoreInterface;
use Spiral\Components\DBAL\Builders\DeleteQuery;
use Spiral\Components\DBAL\Builders\InsertQuery;
use Spiral\Components\DBAL\Builders\SelectQuery;
use Spiral\Components\DBAL\Builders\UpdateQuery;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectableInterface;

class Database extends Component implements InjectableInterface
{
    /**
     * Query and statement events.
     */
    use Component\EventsTrait;

    /**
     * InjectableInterface declares to spiral Container that requested interface or class should
     * not be resolved using default mechanism. Following interface does not require any methods,
     * however class or other interface which inherits InjectableInterface should declare constant
     * named "INJECTION_MANAGER" with name of class responsible for resolving that injection.
     *
     * InjectionFactory will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare injection.
     */
    const INJECTION_MANAGER = DatabaseManager::class;

    /**
     * Transaction isolation level 'SERIALIZABLE'.
     *
     * This is the highest isolation level. With a lock-based concurrency control DBMS implementation,
     * serializability requires read and write locks (acquired on selected data) to be released at
     * the end of the transaction. Also range-locks must be acquired when a SELECT query uses a ranged
     * WHERE clause, especially to avoid the phantom reads phenomenon (see below).
     *
     * When using non-lock based concurrency control, no locks are acquired; however, if the system
     * detects a write collision among several concurrent transactions, only one of them is allowed
     * to commit. See snapshot isolation for more details on this topic.
     *
     * @link http://en.wikipedia.org/wiki/Isolation_(database_systems)
     */
    const ISOLATION_SERIALIZABLE = 'SERIALIZABLE';

    /**
     * Transaction isolation level 'REPEATABLE READ'.
     *
     * In this isolation level, a lock-based concurrency control DBMS implementation keeps read and
     * write locks (acquired on selected data) until the end of the transaction. However, range-locks
     * are not managed, so phantom reads can occur.
     *
     * @link http://en.wikipedia.org/wiki/Isolation_(database_systems)
     */
    const ISOLATION_REPEATABLE_READ = 'REPEATABLE READ';

    /**
     * Transaction isolation level 'READ COMMITTED'.
     *
     * In this isolation level, a lock-based concurrency control DBMS implementation keeps write locks
     * (acquired on selected data) until the end of the transaction, but read locks are released as
     * soon as the SELECT operation is performed (so the non-repeatable reads phenomenon can occur in
     * this isolation level, as discussed below). As in the previous level, range-locks are not managed.
     *
     * Putting it in simpler words, read committed is an isolation level that guarantees that any data
     * read is committed at the moment it is read. It simply restricts the reader from seeing any
     * intermediate, uncommitted, 'dirty' read. It makes no promise whatsoever that if the transaction
     * re-issues the read, it will find the same data; data is free to change after it is read.
     *
     * @link http://en.wikipedia.org/wiki/Isolation_(database_systems)
     */
    const ISOLATION_READ_COMMITTED = 'READ COMMITTED';

    /**
     * Transaction isolation level 'READ UNCOMMITTED'.
     *
     * This is the lowest isolation level. In this level, dirty reads are allowed, so one transaction
     * may see not-yet-committed changes made by other transactions.
     *
     * Since each isolation level is stronger than those below, in that no higher isolation level
     * allows an action forbidden by a lower one, the standard permits a DBMS to run a transaction
     * at an isolation level stronger than that requested (e.g., a "Read committed" transaction may
     * actually be performed at a "Repeatable read" isolation level).
     *
     * @link http://en.wikipedia.org/wiki/Isolation_(database_systems)
     */
    const ISOLATION_READ_UNCOMMITTED = 'READ UNCOMMITTED';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be
     * set to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'DRIVER_SPECIFIC_NOW_EXPRESSION';

    /**
     * Associated driver instance. Driver provides database specific support including correct schema
     * builders, query builders and quote mechanisms.
     *
     * @var Driver
     */
    protected $driver = null;

    /**
     * Container is required to resolve CacheManager when required.
     *
     * @var Container
     */
    protected $container = null;

    /**
     * Database connection name/id.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Database table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * New Database instance. Database class is high level abstraction at top of Driver. Multiple
     * databases can use same driver and be different by table prefix.
     *
     * @param Driver    $driver      Driver instance responsible for database connection.
     * @param Container $container   Container is required to resolve CacheManager component when
     *                               required.
     * @param string    $name        Internal database name/id.
     * @param string    $tablePrefix Default database table prefix, will be used for all table identifiers.
     */
    public function __construct(Driver $driver, Container $container, $name, $tablePrefix = '')
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->setPrefix($tablePrefix);
    }

    /**
     * Internal database name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Associated Driver instance. Driver instances responsible for all database low level operations
     * which can be DBMS specific - such as connection preparation, custom table/column/index/reference
     * schemas and etc.
     *
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Change database table prefix.
     *
     * @param string $tablePrefix
     * @return $this
     */
    public function setPrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;

        return $this;
    }

    /**
     * Get current table prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Get prepared PDOStatement instance. Query will be run against connected PDO object (inside Driver).
     *
     * @param string $query      SQL statement with parameter placeholders.
     * @param array  $parameters Parameters to be binded into query.
     * @return \PDOStatement
     */
    public function statement($query, array $parameters = [])
    {
        return $this->event('statement', [
            'statement'  => $this->driver->statement($query, $parameters),
            'query'      => $query,
            'parameters' => $parameters,
            'database'   => $this
        ])['statement'];
    }

    /**
     * Run affect (DELETE, UPDATE) type SQL statement with prepare parameters against connected PDO
     * instance. Count of affected rows will be returned.
     *
     * @param string $query      SQL statement with parameter placeholders.
     * @param array  $parameters Parameters to be binded into query.
     * @return int
     * @throws \PDOException
     */
    public function affect($query, array $parameters = [])
    {
        return $this->statement($query, $parameters)->rowCount();
    }

    /**
     * Alias for affect() method. Run affect (DELETE, UPDATE) type SQL statement with prepare parameters
     * against connected PDO instance. Count of affected rows will be returned.
     *
     * @param string $query      SQL statement with parameter placeholders.
     * @param array  $parameters Parameters to be binded into query.
     * @return int
     * @throws \PDOException
     */
    public function execute($query, array $parameters = [])
    {
        return $this->statement($query, $parameters)->rowCount();
    }

    /**
     * Run "select" type SQL statement with prepare parameters against connected PDO instance.
     * QueryResult will be returned and can be used to walk thought resulted dataset.
     *
     * @param string $query      SQL statement with parameter placeholders.
     * @param array  $parameters Parameters to be binded into query.
     * @return QueryResult
     * @throws \PDOException
     */
    public function query($query, array $parameters = [])
    {
        return $this->event('query', [
            'statement'  => $this->driver->query($query, $parameters),
            'query'      => $query,
            'parameters' => $parameters,
            'database'   => $this
        ])['statement'];
    }

    /**
     * Run select type SQL statement with prepare parameters against connected PDO instance. Result
     * will be cached for desired lifetime and presented by CachedResult data reader.
     *
     * @param int            $lifetime   Cache lifetime in seconds.
     * @param string         $query      SQL statement with parameter placeholders.
     * @param array          $parameters Parameters to be binded into query.
     * @param string         $key        Cache key to be used to store query result.
     * @param StoreInterface $store      Cache store to store result in, if null default store will
     *                                   be used.
     * @return CachedResult
     * @throws CacheException
     */
    public function cached(
        $lifetime,
        $query,
        array $parameters = [],
        $key = '',
        StoreInterface $store = null
    )
    {
        $store = !empty($store) ? $store : CacheManager::getInstance($this->container)->store();

        if (empty($key))
        {
            /**
             * Trying to build unique query id based on provided options and environment.
             */
            $key = md5(serialize([
                $query,
                $parameters,
                $this->name,
                $this->tablePrefix
            ]));
        }

        $data = $store->remember($key, $lifetime, function () use ($query, $parameters)
        {
            return $this->query($query, $parameters)->fetchAll();
        });

        return new CachedResult($store, $key, $query, $parameters, $data);
    }

    /**
     * Get database specified select query builder, as builder called outside parent table, from()
     * method should be called to provide tables to select data from. Columns can be provided as
     * array, comma separated string or multiple parameters.
     *
     * @param array|string $columns Columns to select.
     * @return SelectQuery
     */
    public function select($columns = '*')
    {
        $columns = func_get_args();
        if (is_array($columns) && isset($columns[0]) && is_array($columns[0]))
        {
            //Can be required in some cases while collecting data from Table->select(), stupid bug.
            $columns = $columns[0];
        }

        return $this->driver->selectBuilder($this, ['columns' => $columns]);
    }

    /**
     * Get InsertQuery builder with driver specific query compiler and associated with current database.
     *
     * @param string $table Table where values should be inserted to.
     * @return InsertQuery
     */
    public function insert($table = '')
    {
        return $this->driver->insertBuilder($this, compact('table'));
    }

    /**
     * Get DeleteQuery builder with driver specific query compiler and associated with current database.
     *
     * @param string $table Table where rows should be deleted from.
     * @param array  $where Initial set of where rules specified as array.
     * @return DeleteQuery
     */
    public function delete($table = '', array $where = [])
    {
        return $this->driver->deleteBuilder($this, compact('table', 'where'));
    }

    /**
     * Get UpdateQuery builder with driver specific query compiler and associated with current database.
     *
     * @param string $table  Table where rows should be updated in.
     * @param array  $values Initial set of columns to update associated with their values.
     * @param array  $where  Initial set of where rules specified as array.
     * @return UpdateQuery
     */
    public function update($table = '', array $values = [], array $where = [])
    {
        return $this->driver->updateBuilder($this, compact('table', 'values', 'where'));
    }

    /**
     * Start SQL transaction with specified isolation level, not all database types support it.
     * Nested transactions will be processed using savepoints.
     *
     * @link http://en.wikipedia.org/wiki/Database_transaction
     * @link http://en.wikipedia.org/wiki/Isolation_(database_systems)
     * @param string $isolationLevel No value provided by default.
     * @return bool
     */
    public function beginTransaction($isolationLevel = null)
    {
        return $this->driver->beginTransaction($isolationLevel);
    }

    /**
     * Commit the active database transaction.
     *
     * @return bool
     */
    public function commitTransaction()
    {
        return $this->driver->commitTransaction();
    }

    /**
     * Rollback the active database transaction.
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->driver->rollbackTransaction();
    }

    /**
     * Simplified way to perform set of SQL commands inside transaction, user callback as closure
     * function which will receive current database instance as only one argument.
     *
     * @param callable $callback       Closure or callback, function will receive current database
     *                                 instance as only one argument.
     * @param string   $isolationLevel No value provided by default.
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback, $isolationLevel = null)
    {
        $this->beginTransaction($isolationLevel);

        try
        {
            $result = call_user_func($callback, $this);
            $this->commitTransaction();

            return $result;
        }
        catch (\Exception $exception)
        {
            $this->rollBack();
            throw $exception;
        }
    }

    /**
     * Get all available database tables (only tables matching table prefix will be selected).
     *
     * @return Table[]
     */
    public function getTables()
    {
        $result = [];
        foreach ($this->driver->tableNames() as $table)
        {
            if ($this->tablePrefix && strpos($table, $this->tablePrefix) !== 0)
            {
                continue;
            }

            $result[] = $this->table(substr($table, strlen($this->tablePrefix)));
        }

        return $result;
    }

    /**
     * Check if linked database has specified table.
     *
     * @param string $name Table name without prefix.
     * @return bool
     */
    public function hasTable($name)
    {
        return $this->driver->hasTable($this->tablePrefix . $name);
    }

    /**
     * Get instance of database table, table can be used as enterpoint to query builders, table
     * schema and other operations.
     *
     * @param string $name Table name without prefix.
     * @return Table
     */
    public function table($name)
    {
        return new Table($name, $this);
    }

    /**
     * Get instance of database table, table can be used as enterpoint to query builders and table
     * schema and other operations.
     *
     * @param string $name Table name without prefix.
     * @return Table
     */
    public function __get($name)
    {
        return $this->table($name);
    }
}