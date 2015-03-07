<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Sqlite;

use Spiral\Components\DBAL\Driver;

class SqliteDriver extends Driver
{
    /**
     * Get short name to use for driver query profiling.
     */
    const DRIVER_NAME = 'SQLite';

    /**
     * Class names should be used to create schema instances to describe specified driver table. Schema realizations are
     * driver specific and allows both schema reading and writing (migrations).
     */
    const SCHEMA_TABLE     = 'Spiral\Components\DBAL\Drivers\Sqlite\TableSchema';
    const SCHEMA_COLUMN    = 'Spiral\Components\DBAL\Drivers\Sqlite\ColumnSchema';
    const SCHEMA_INDEX     = 'Spiral\Components\DBAL\Drivers\Sqlite\IndexSchema';
    const SCHEMA_REFERENCE = 'Spiral\Components\DBAL\Drivers\Sqlite\ReferenceSchema';

    /**
     * Class name should be used to represent driver specific QueryGrammar.
     */
    const QUERY_COMPILER = 'Spiral\Components\DBAL\Drivers\Sqlite\QueryCompiler';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be set to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'CURRENT_TIMESTAMP';

    /**
     * SQL query to fetch table names from database. Declared as constant only because i love well organized things.
     *
     * @var string
     */
    const FETCH_TABLES_QUERY = "SELECT * FROM sqlite_master WHERE type = 'table'";

    /**
     * Query to check table existence.
     *
     * @var string
     */
    const TABLE_EXISTS_QUERY = "SELECT sql FROM sqlite_master WHERE type = 'table' and name = ?";

    /**
     * Driver instances responsible for all database low level operations which can be DBMS specific - such as connection
     * preparation, custom table/column/index/reference schemas and etc.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        //Removing "sqlite:"
        $this->databaseName = substr($this->config['connection'], 7);
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
     * Get list of all available database table names.
     *
     * @return array
     */
    public function tableNames()
    {
        $tables = array();
        foreach ($this->query(static::FETCH_TABLES_QUERY) as $table)
        {
            if ($table['name'] != 'sqlite_sequence')
            {
                $tables[] = $table['name'];
            }
        }

        return $tables;
    }

    /**
     * Clean (truncate) specified database table. Table should exists at this moment.
     *
     * @param string $table Table name without prefix included.
     */
    public function truncateTable($table)
    {
        $this->statement("DELETE FROM {$this->identifier($table)}");
    }

    /**
     * Set transaction isolation level, this feature may not be supported by specific database driver.
     *
     * @param string $level
     */
    public function isolationLevel($level)
    {
        $this->logger()->error("Transaction isolation level is not fully supported by SQLite ({level}).", compact('level'));
    }
}