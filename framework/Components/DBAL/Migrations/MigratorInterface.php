<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Migrations;

use Spiral\Components\DBAL\Database;

interface MigratorInterface
{
    /**
     * New migrator instance. Migrator responsible for running migrations and keeping track of what was already executed.
     *
     * @param Repository $repository
     * @param Database   $database
     * @param array      $config
     */
    public function __construct(Repository $repository, Database $database, $config);

    /**
     * Check if current environment is safe to run migrations.
     *
     * @return bool
     */
    public function isSafe();

    /**
     * Check if migrator are set and can be used. Default migrator will check that migrations table exists in associated
     * database.
     *
     * @return bool
     */
    public function isConfigured();

    /**
     * Configure migrator (create tables, files and etc).
     */
    public function configure();

    /**
     * Get list of all migrations with their class names, file names, status and migrated time (if presented).
     *
     * @return array
     */
    public function getMigrations();

    /**
     * Run one outstanding migration, migrations will be performed in an order they were registered.
     */
    public function run();

    /**
     * Rollback last executed migration.
     */
    public function rollback();
}