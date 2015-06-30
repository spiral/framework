<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Components\DBAL\Builders\DeleteQuery;
use Spiral\Components\DBAL\Builders\InsertQuery;
use Spiral\Components\DBAL\Builders\SelectQuery;
use Spiral\Components\DBAL\Builders\UpdateQuery;
use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;
use Spiral\Components\DBAL\Schemas\AbstractIndexSchema;
use Spiral\Components\DBAL\Schemas\AbstractReferenceSchema;
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;
use Spiral\Core\Component;
use PDO;
use PDOStatement;
use Spiral\Core\Container;

abstract class Driver extends Component
{
    /**
     * Profiling and logging.
     */
    use Component\LoggerTrait, Component\EventsTrait;

    /**
     * Get short name to use for driver query profiling.
     */
    const DRIVER_NAME = 'DBALDriver';

    /**
     * Class names should be used to create schema instances to describe specified driver table.
     * Schema realizations are driver specific and allows both schema reading and writing (migrations).
     */
    const SCHEMA_TABLE     = '';
    const SCHEMA_COLUMN    = '';
    const SCHEMA_INDEX     = '';
    const SCHEMA_REFERENCE = '';

    /**
     * Class name should be used to represent single query rowset.
     */
    const QUERY_RESULT = 'Spiral\Components\DBAL\QueryResult';

    /**
     * Class name should be used to represent driver specific QueryCompiler.
     */
    const QUERY_COMPILER = 'Spiral\Components\DBAL\QueryCompiler';

    /**
     * DateTime format should be used to perform automatic conversion of DateTime objects.
     *
     * @var string
     */
    const DATETIME = 'Y-m-d H:i:s';

    /**
     * Default datetime value.
     */
    const DEFAULT_DATETIME = '1970-01-01 00:00:00';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be
     * set to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'DRIVER_SPECIFIC_NOW_EXPRESSION';

    /**
     * Connection configuration described in DBAL config file. Any driver can be used as data source
     * for multiple databases as table prefix and quotation defined on Database instance level.
     *
     * @var array
     */
    protected $config = [
        'connection' => '',
        'username'   => '',
        'password'   => '',
        'profiling'  => true,
        'options'    => []
    ];

    /**
     * Database name (fetched from connection string). In some cases can contain empty string (SQLite).
     *
     * @var string
     */
    protected $databaseName = '';

    /**
     * Default driver PDO options set, this keys will be merged with data provided by DBAL configuration.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false
    ];

    /**
     * Container.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * PDO connection.
     *
     * @var PDO
     */
    protected $pdo = null;

    /**
     * Current transaction level (count of nested transactions). Not all drives can support nested
     * transactions.
     *
     * @var int
     */
    protected $transactionLevel = 0;

    /**
     * Driver instances responsible for all database low level operations which can be DBMS specific
     * - such as connection preparation, custom table/column/index/reference schemas and etc.
     *
     * @param array     $config
     * @param Container $container
     */
    public function __construct(array $config = [], Container $container)
    {
        $this->config = $config + $this->config;
        $this->container = $container;

        $this->options = $config['options'] + $this->options;

        if (preg_match('/(?:dbname|database)=([^;]+)/i', $this->config['connection'], $matches))
        {
            $this->databaseName = $matches[1];
        }
    }

    /**
     * Get Driver configuration data.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get database name (fetched from connection string). In some cases can return empty string
     * (SQLite).
     *
     * @return string|null
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * While profiling enabled driver will create query logging and benchmarking events. This is
     * recommended option on development environment.
     *
     * @param bool $enabled Enable or disable driver profiling.
     * @return static
     */
    public function profiling($enabled = true)
    {
        $this->config['profiling'] = $enabled;

        return $this;
    }

    /**
     * Check if profiling mode is enabled.
     *
     * @return bool
     */
    public function isProfiling()
    {
        return $this->config['profiling'];
    }

    /**
     * Check if PDO already constructed and ready for use.
     *
     * @return bool
     */
    public function isConnected()
    {
        return (bool)$this->pdo;
    }

    /**
     * Get associated PDO connection. Driver will automatically connect to PDO if it's not already
     * exists.
     *
     * @return PDO
     */
    public function getPDO()
    {
        if (!empty($this->pdo))
        {
            return $this->pdo;
        }

        benchmark(static::DRIVER_NAME . "::connect", $this->config['connection']);
        $this->pdo = $this->event('connect', $this->createPDO());
        benchmark(static::DRIVER_NAME . "::connect", $this->config['connection']);

        return $this->pdo;
    }

    /**
     * Manually set associated PDO instance.
     *
     * @param PDO $pdo
     * @return static
     */
    public function setPDO(PDO $pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Method used to get PDO instance for current driver, it can be overwritten by custom driver
     * realization to perform DBMS specific operations.
     *
     * @return PDO
     */
    protected function createPDO()
    {
        return new PDO(
            $this->config['connection'],
            $this->config['username'],
            $this->config['password'],
            $this->options
        );
    }

    /**
     * Disconnect PDO.
     *
     * @return static
     */
    public function disconnect()
    {
        $this->event('disconnect', $this->pdo);
        $this->pdo = null;

        return $this;
    }

    /**
     * Driver specific database/table identifier quotation.
     *
     * @param string $identifier Table or column name (no dots or other parts allowed).
     * @return string
     */
    public function identifier($identifier)
    {
        return $identifier == '*' ? '*' : '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Driver specific PDOStatement parameters preparation.
     *
     * @param array $parameters
     * @return array
     */
    public function prepareParameters(array $parameters)
    {
        $result = [];
        foreach ($parameters as $parameter)
        {
            if ($parameter instanceof ParameterInterface)
            {
                $parameter = $parameter->getValue();
            }

            if ($parameter instanceof \DateTime)
            {
                //We are going to convert all timestamps to database timezone which is UTC by default
                $parameter = $parameter->setTimezone(
                    new \DateTimeZone(DatabaseManager::DEFAULT_TIMEZONE)
                )->format(static::DATETIME);
            }

            if (is_array($parameter))
            {
                $result = array_merge(
                    $result,
                    $this->prepareParameters($parameter)
                );
            }
            else
            {
                $result[] = $parameter;
            }
        }

        return $result;
    }

    /**
     * Get prepared PDOStatement instance. Query will be run against connected PDO object.
     *
     * @param string $query              SQL statement with parameter placeholders.
     * @param array  $parameters         Parameters to be binded into query.
     * @param array  $preparedParameters Processed parameters will be saved into this array.
     * @return PDOStatement
     */
    public function statement($query, array $parameters = [], &$preparedParameters = null)
    {
        $preparedParameters = $parameters = $this->prepareParameters($parameters);

        try
        {
            if ($this->config['profiling'])
            {
                $builtQuery = DatabaseManager::interpolateQuery($query, $parameters);
                benchmark(static::DRIVER_NAME . "::" . $this->getDatabaseName(), $builtQuery);
            }

            $pdoStatement = $this->getPDO()->prepare($query);
            $pdoStatement->execute($parameters);

            $this->event('statement', [
                'statement'  => $pdoStatement,
                'query'      => $query,
                'parameters' => $parameters
            ]);

            if ($this->config['profiling'] && isset($builtQuery))
            {
                benchmark(static::DRIVER_NAME . "::" . $this->getDatabaseName(), $builtQuery);
                self::logger()->debug($builtQuery, compact('query', 'parameters'));
            }
        }
        catch (\PDOException $exception)
        {
            self::logger()->error(
                !empty($builtQuery)
                    ? $builtQuery
                    : DatabaseManager::interpolateQuery($query, $parameters),
                compact('query', 'parameters')
            );

            throw $exception;
        }

        return $pdoStatement;
    }

    /**
     * Run select type SQL statement with prepare parameters against connected PDO instance.
     * QueryResult will be returned and can be used to walk thought resulted dataset.
     *
     * @param string $query              SQL statement with parameter placeholders.
     * @param array  $parameters         Parameters to be binded into query.
     * @param array  $preparedParameters Processed parameters will be saved into this array.
     * @return QueryResult
     * @throws \PDOException
     */
    public function query($query, array $parameters = [], &$preparedParameters = null)
    {
        return $this->container->get(static::QUERY_RESULT, [
            'statement'  => $this->statement($query, $parameters, $preparedParameters),
            'parameters' => $preparedParameters
        ]);
    }

    /**
     * Get last inserted row id.
     *
     * @param string|null $sequence Name of the sequence object from which the ID should be returned.
     *                              Not required for MySQL database, but should be specified for
     *                              Postgres (Postgres Driver will do it automatically).
     * @return mixed
     */
    public function lastInsertID($sequence = null)
    {
        return $sequence
            ? (int)$this->getPDO()->lastInsertId($sequence)
            : (int)$this->getPDO()->lastInsertId();
    }

    /**
     * Get the number of active transactions (transaction level).
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactionLevel;
    }

    /**
     * Start SQL transaction with specified isolation level, not all database types support it.
     * Nested transactions will be processed using savepoints.
     *
     * @link   http://en.wikipedia.org/wiki/Database_transaction
     * @link   http://en.wikipedia.org/wiki/Isolation_(database_systems)
     * @param string $isolationLevel No value provided by default.
     * @return bool
     */
    public function beginTransaction($isolationLevel = null)
    {
        $this->transactionLevel++;
        if ($this->transactionLevel == 1)
        {
            if (!empty($isolationLevel))
            {
                $this->isolationLevel($isolationLevel);
            }

            self::logger()->info('Starting transaction.');

            return $this->getPDO()->beginTransaction();
        }
        else
        {
            $this->savepointCreate($this->transactionLevel);
        }

        return true;
    }

    /**
     * Commit the active database transaction.
     *
     * @return bool
     */
    public function commitTransaction()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel == 0)
        {
            self::logger()->info('Committing transaction.');

            return $this->getPDO()->commit();
        }
        else
        {
            $this->savepointRelease($this->transactionLevel + 1);
        }

        return true;
    }

    /**
     * Rollback the active database transaction.
     *
     * @return bool
     */
    public function rollbackTransaction()
    {
        $this->transactionLevel--;

        if ($this->transactionLevel == 0)
        {
            self::logger()->info('Rolling black transaction.');

            return $this->getPDO()->rollBack();
        }
        else
        {
            $this->savepointRollback($this->transactionLevel + 1);
        }

        return true;
    }

    /**
     * Set transaction isolation level, this feature may not be supported by specific database driver.
     *
     * @param string $level
     */
    protected function isolationLevel($level)
    {
        self::logger()->info("Setting transaction isolation level to '{$level}'.");
        !empty($level) && $this->statement("SET TRANSACTION ISOLATION LEVEL {$level}");
    }

    /**
     * Create nested transaction save point.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     * @param string $name Savepoint name/id, must not contain spaces and be valid database identifier.
     */
    protected function savepointCreate($name)
    {
        self::logger()->info("Creating savepoint '{$name}'.");
        $this->statement("SAVEPOINT " . $this->identifier("SVP{$name}"));
    }

    /**
     * Commit/release savepoint.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     * @param string $name Savepoint name/id, must not contain spaces and be valid database identifier.
     */
    protected function savepointRelease($name)
    {
        self::logger()->info("Releasing savepoint '{$name}'.");
        $this->statement("RELEASE SAVEPOINT " . $this->identifier("SVP{$name}"));
    }

    /**
     * Rollback savepoint.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     * @param string $name Savepoint name/id, must not contain spaces and be valid database identifier.
     */
    protected function savepointRollback($name)
    {
        self::logger()->info("Rolling back savepoint '{$name}'.");
        $this->statement("ROLLBACK TO SAVEPOINT " . $this->identifier("SVP{$name}"));
    }

    /**
     * Check if linked database has specified table.
     *
     * @param string $name Fully specified table name, including prefix.
     * @return bool
     */
    abstract public function hasTable($name);

    /**
     * Fetch list of all available table names under linked database, this method is called by Database
     * in getTables() method, same methods will automatically filter tables by their prefix.
     *
     * @return array
     */
    abstract public function tableNames();

    /**
     * Clean (truncate) specified database table. Table should exists at this moment.
     *
     * @param string $table Table name without prefix included.
     */
    public function truncateTable($table)
    {
        $this->statement("TRUNCATE TABLE {$this->identifier($table)}");
    }

    /**
     * Get schema for specified table name, name should be provided without database prefix.
     * TableSchema contains information about all table columns, indexes and foreign keys. Schema can
     * be used to manipulate table structure.
     *
     * @param string $table       Table name without prefix included.
     * @param string $tablePrefix Database specific table prefix, this parameter is not required,
     *                            but if provided all
     *                            foreign keys will be created using it.
     * @return AbstractTableSchema
     */
    public function tableSchema($table, $tablePrefix = '')
    {
        return $this->container->get(
            static::SCHEMA_TABLE,
            [
                'driver'      => $this,
                'name'        => $table,
                'tablePrefix' => $tablePrefix
            ]
        );
    }

    /**
     * Get instance of driver specified ColumnSchema. Every schema object should fully represent one
     * table column, it's type and all possible options.
     *
     * @param AbstractTableSchema $table  Parent TableSchema.
     * @param string              $name   Column name.
     * @param mixed               $schema Driver specific column schema.
     * @return AbstractColumnSchema
     */
    public function columnSchema(AbstractTableSchema $table, $name, $schema = null)
    {
        return $this->container->get(static::SCHEMA_COLUMN, compact('table', 'name', 'schema'));
    }

    /**
     * Get instance of driver specified IndexSchema. Every index schema should represent single table
     * index including name, type and columns.
     *
     * @param AbstractTableSchema $table  Parent TableSchema.
     * @param string              $name   Index name.
     * @param mixed               $schema Driver specific index schema.
     * @return AbstractIndexSchema
     */
    public function indexSchema(AbstractTableSchema $table, $name, $schema = null)
    {
        return $this->container->get(static::SCHEMA_INDEX, compact('table', 'name', 'schema'));
    }

    /**
     * Get instance of driver specified ReferenceSchema (foreign key). Every ReferenceSchema should
     * represent one foreign key with it's referenced table, column and rules.
     *
     * @param AbstractTableSchema $table  Parent TableSchema.
     * @param string              $name   Constraint name.
     * @param mixed               $schema Driver specific foreign key schema.
     * @return AbstractReferenceSchema
     */
    public function referenceSchema(AbstractTableSchema $table, $name, $schema = null)
    {
        return $this->container->get(static::SCHEMA_REFERENCE, compact('table', 'name', 'schema'));
    }

    /**
     * Current timestamp expression value.
     *
     * @return string
     */
    public function timestampNow()
    {
        return static::TIMESTAMP_NOW;
    }

    /**
     * QueryCompiler is low level SQL compiler which used by different query builders to generate
     * statement based on provided tokens. Every builder will get it's own QueryCompiler at it has
     * some internal isolation features (such as query specific table aliases).
     *
     * @param string $tablePrefix Database specific table prefix, used to correctly quote table names
     *                            and other identifiers.
     * @return QueryCompiler
     */
    public function queryCompiler($tablePrefix = '')
    {
        return $this->container->get(
            static::QUERY_COMPILER,
            [
                'driver'      => $this,
                'tablePrefix' => $tablePrefix
            ]
        );
    }

    /**
     * Get InsertQuery builder with driver specific query compiler.
     *
     * @param Database $database   Database instance builder should be associated to.
     * @param array    $parameters Initial builder parameters.
     * @return InsertQuery
     */
    public function insertBuilder(Database $database, array $parameters = [])
    {
        return InsertQuery::make([
                'database' => $database,
                'compiler' => $this->queryCompiler($database->getPrefix())
            ] + $parameters, $this->container);
    }

    /**
     * Get SelectQuery builder with driver specific query compiler.
     *
     * @param Database $database   Database instance builder should be associated to.
     * @param array    $parameters Initial builder parameters.
     * @return SelectQuery
     */
    public function selectBuilder(Database $database, array $parameters = [])
    {
        return SelectQuery::make([
                'database' => $database,
                'compiler' => $this->queryCompiler($database->getPrefix())
            ] + $parameters, $this->container);
    }

    /**
     * Get DeleteQuery builder with driver specific query compiler.
     *
     * @param Database $database   Database instance builder should be associated to.
     * @param array    $parameters Initial builder parameters.
     * @return DeleteQuery
     */
    public function deleteBuilder(Database $database, array $parameters = [])
    {
        return DeleteQuery::make([
                'database' => $database,
                'compiler' => $this->queryCompiler($database->getPrefix())
            ] + $parameters, $this->container);
    }

    /**
     * Get UpdateQuery builder with driver specific query compiler.
     *
     * @param Database $database   Database instance builder should be associated to.
     * @param array    $parameters Initial builder parameters.
     * @return UpdateQuery
     */
    public function updateBuilder(Database $database, array $parameters = [])
    {
        return UpdateQuery::make([
                'database' => $database,
                'compiler' => $this->queryCompiler($database->getPrefix())
            ] + $parameters, $this->container);
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'connection' => $this->config['connection'],
            'connected'  => $this->isConnected(),
            'database'   => $this->getDatabaseName(),
            'options'    => $this->options
        ];
    }
}