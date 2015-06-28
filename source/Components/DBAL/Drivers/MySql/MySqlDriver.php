<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\MySql;

use Spiral\Components\DBAL\Driver;
use PDO;

class MySqlDriver extends Driver
{
    /**
     * Get short name to use for driver query profiling.
     */
    const DRIVER_NAME = 'MySQL';

    /**
     * Class names should be used to create schema instances to describe specified driver table. Schema
     * realizations are driver specific and allows both schema reading and writing (migrations).
     */
    const SCHEMA_TABLE     = 'Spiral\Components\DBAL\Drivers\MySql\TableSchema';
    const SCHEMA_COLUMN    = 'Spiral\Components\DBAL\Drivers\MySql\ColumnSchema';
    const SCHEMA_INDEX     = 'Spiral\Components\DBAL\Drivers\MySql\IndexSchema';
    const SCHEMA_REFERENCE = 'Spiral\Components\DBAL\Drivers\MySql\ReferenceSchema';

    /**
     * Class name should be used to represent driver specific QueryGrammar.
     */
    const QUERY_COMPILER = 'Spiral\Components\DBAL\Drivers\MySql\QueryCompiler';

    /**
     * Statement should be used for ColumnSchema to indicate that default datetime value should be
     * set to current time.
     *
     * @var string
     */
    const TIMESTAMP_NOW = 'CURRENT_TIMESTAMP';

    /**
     * Default driver PDO options set, this keys will be merged with data provided by DBAL configuration.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE               => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"'
    ];

    /**
     * Query to check table existence.
     *
     * @var string
     */
    const TABLE_EXISTS_QUERY = "SELECT COUNT(*) FROM information_schema.tables
                                WHERE table_schema = ? AND table_name = ?";

    /**
     * Check if linked database has specified table.
     *
     * @param string $name Fully specified table name, including prefix.
     * @return bool
     */
    public function hasTable($name)
    {
        return (bool)$this->query(self::TABLE_EXISTS_QUERY, [$this->databaseName, $name])
            ->fetchColumn();
    }

    /**
     * Get list of all available database table names.
     *
     * @return array
     */
    public function tableNames()
    {
        $tables = [];
        foreach ($this->query('SHOW TABLES')->fetchMode(PDO::FETCH_NUM) as $row)
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
        return $identifier == '*' ? '*' : '`' . str_replace('`', '``', $identifier) . '`';
    }
}