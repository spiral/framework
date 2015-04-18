<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\SqlServer;

use Spiral\Components\DBAL\Driver;
use PDO;

class SqlServerDriver extends Driver
{
    /**
     * Get short name to use for driver query profiling.
     */
    const DRIVER_NAME = 'SQLServer';

    /**
     * Class names should be used to create schema instances to describe specified driver table.
     * Schema realizations are driver specific and allows both schema reading and writing (migrations).
     */
    const SCHEMA_TABLE     = 'Spiral\Components\DBAL\Drivers\SqlServer\TableSchema';
    const SCHEMA_COLUMN    = 'Spiral\Components\DBAL\Drivers\SqlServer\ColumnSchema';
    const SCHEMA_INDEX     = 'Spiral\Components\DBAL\Drivers\SqlServer\IndexSchema';
    const SCHEMA_REFERENCE = 'Spiral\Components\DBAL\Drivers\SqlServer\ReferenceSchema';

    /**
     * Class name should be used to represent single query rowset.
     */
    const QUERY_RESULT = 'Spiral\Components\DBAL\Drivers\SqlServer\QueryResult';

    /**
     * Class name should be used to represent driver specific QueryGrammar.
     */
    const QUERY_COMPILER = 'Spiral\Components\DBAL\Drivers\SqlServer\QueryCompiler';

    /**
     * DateTime format should be used to perform automatic conversion of DateTime objects.
     *
     * @var string
     */
    const DATETIME = 'Y-m-d\TH:i:s';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be
     * set to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'getdate()';

    /**
     * SQL query to fetch table names from database. Declared as constant only because i love well
     * organized things.
     *
     * @var string
     */
    const FETCH_TABLES_QUERY = "SELECT table_name FROM information_schema.tables
                                WHERE table_type = 'BASE TABLE'";

    /**
     * Query to check table existence.
     *
     * @var string
     */
    const TABLE_EXISTS_QUERY = "SELECT COUNT(*) FROM information_schema.tables
                                WHERE table_type = 'BASE TABLE' AND table_name = ?";

    /**
     * Default driver PDO options set, this keys will be merged with data provided by DBAL configuration.
     *
     * @var array
     */
    protected $options = array(
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false
    );

    /**
     * Getting SQLServer version. Required for better LIMIT/OFFSET syntax.
     *
     * @link http://stackoverflow.com/questions/2135418/equivalent-of-limit-and-offset-for-sql-server
     * @var int
     */
    protected $serverVersion = 0;

    /**
     * Getting SQLServer version.
     *
     * @link http://stackoverflow.com/questions/2135418/equivalent-of-limit-and-offset-for-sql-server
     * @return int
     */
    public function getServerVersion()
    {
        if (!$this->serverVersion)
        {
            $this->serverVersion = (int)$this->getPDO()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        }

        return $this->serverVersion;
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
        foreach ($this->query(self::FETCH_TABLES_QUERY)->fetchMode(PDO::FETCH_NUM) as $row)
        {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * Driver specific database/table identifier quotation.
     *
     * @param string $identifier Table or column name (no dots or other parts allowed).
     * @return string
     */
    public function identifier($identifier)
    {
        return $identifier == '*' ? '*' : '[' . str_replace('[', '[[', $identifier) . ']';
    }
}