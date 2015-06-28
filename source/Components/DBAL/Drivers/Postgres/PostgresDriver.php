<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\Driver;
use PDO;
use Spiral\Components\DBAL\Drivers\Postgres\Builders\InsertQuery;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\RuntimeCacheInterface;

class PostgresDriver extends Driver
{
    /**
     * Get short name to use for driver query profiling.
     */
    const DRIVER_NAME = 'Postgres';

    /**
     * Class names should be used to create schema instances to describe specified driver table.
     * Schema realizations are driver specific and allows both schema reading and writing (migrations).
     */
    const SCHEMA_TABLE     = 'Spiral\Components\DBAL\Drivers\Postgres\TableSchema';
    const SCHEMA_COLUMN    = 'Spiral\Components\DBAL\Drivers\Postgres\ColumnSchema';
    const SCHEMA_INDEX     = 'Spiral\Components\DBAL\Drivers\Postgres\IndexSchema';
    const SCHEMA_REFERENCE = 'Spiral\Components\DBAL\Drivers\Postgres\ReferenceSchema';

    /**
     * Class name should be used to represent driver specific QueryGrammar.
     */
    const QUERY_COMPILER = 'Spiral\Components\DBAL\Drivers\Postgres\QueryCompiler';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be set
     * to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'now()';

    /**
     * SQL query to fetch table names from database. Declared as constant only because i love well
     * organized things.
     *
     * @var string
     */
    const FETCH_TABLES_QUERY = "SELECT table_name FROM information_schema.tables
                                WHERE table_schema = 'public' AND table_type = 'BASE TABLE'";

    /**
     * Query to check table existence.
     *
     * @var string
     */
    const TABLE_EXISTS_QUERY = "SELECT table_name FROM information_schema.tables
                                WHERE table_schema = 'public'
                                AND table_type = 'BASE TABLE'
                                AND table_name = ?";

    /**
     * CoreInterface.
     *
     * @var CoreInterface
     */
    protected $runtime = null;

    /**
     * Due postgres sequences mechanism we have two options to get last inserted id with valid value,
     * use nextval() sequence, or use RETURN statement. Due we have ability to analyze any table, let's
     * store primary keys in cache.
     *
     * @var array
     */
    protected $primaryKeys = array();

    /**
     * Driver instances responsible for all database low level operations which can be DBMS specific
     * - such as connection preparation, custom table/column/index/reference schemas and etc.
     *
     * @param array                 $config
     * @param Container             $container
     * @param RuntimeCacheInterface $runtime
     */
    public function __construct(
        array $config = array(),
        Container $container,
        RuntimeCacheInterface $runtime
    )
    {
        parent::__construct($config, $container);
        $this->runtime = $runtime;
    }

    /**
     * Method used to get PDO instance for current driver, it can be overwritten by custom driver
     * realization to perform DBMS specific operations.
     *
     * @return PDO
     */
    protected function createPDO()
    {
        //Spiral is purely UTF-8
        $pdo = parent::createPDO();
        $pdo->exec("SET NAMES 'UTF-8'");

        return $pdo;
    }

    /**
     * Get primary key name for dedicated table, used by InsertQuery to generate insert statement.
     * Attention, DO NOT use this function by yourself. It will be likely erased or modified in future
     * versions and replaced with ID reservation based on sequence name. If you need table primary key
     * use table schemas.
     *
     * @param string $table Fully specified table name, including postfix.
     * @return string
     * @throws DBALException
     */
    public function getPrimary($table)
    {
        if (empty($this->primaryKeys))
        {
            $this->primaryKeys = $this->runtime->loadData($this->getDatabaseName() . '-primary');
        }

        if (!empty($this->primaryKeys) && array_key_exists($table, $this->primaryKeys))
        {
            return $this->primaryKeys[$table];
        }

        if (!$this->hasTable($table))
        {
            throw new DBALException(
                "Unable to fetch table primary key, no such table '{$table}' exists."
            );
        }

        $this->primaryKeys[$table] = $this->tableSchema($table)->getPrimaryKeys();

        if (count($this->primaryKeys[$table]) > 2)
        {
            //Compound keys are not supported
            $this->primaryKeys[$table] = null;
        }

        $this->primaryKeys[$table] = $this->primaryKeys[$table][0];

        //Caching
        $this->runtime->saveData($this->getDatabaseName() . '-primary', $this->primaryKeys);

        return $this->primaryKeys[$table];
    }

    /**
     * Check if linked database has specified table.
     *
     * @param string $name Fully specified table name, including prefix.
     * @return bool
     */
    public function hasTable($name)
    {
        return (bool)$this->query(self::TABLE_EXISTS_QUERY, array($name))->fetchColumn();
    }

    /**
     * Fetch list of all available table names under linked database, this method is called by Database
     * in getTables() method, same methods will automatically filter tables by their prefix.
     *
     * @return array
     */
    public function tableNames()
    {
        $tables = array();
        foreach ($this->query(static::FETCH_TABLES_QUERY) as $row)
        {
            $tables[] = $row['table_name'];
        }

        return $tables;
    }

    /**
     * Get InsertQuery builder with driver specific query compiler. Postgres uses custom query
     * realization with automatic primary key name resolution. In future it should work based on id
     * reservation.
     *
     * @param Database $database   Database instance builder should be associated to.
     * @param array    $parameters Initial builder parameters.
     * @return InsertQuery
     */
    public function insertBuilder(Database $database, array $parameters = array())
    {
        return InsertQuery::make(array(
                'database' => $database,
                'compiler' => $this->queryCompiler($database->getPrefix())
            ) + $parameters, $this->container);
    }
}