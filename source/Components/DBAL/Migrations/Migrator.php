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
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Table;
use Spiral\Core\Component;
use Spiral\Core\Core;
use Spiral\Support\Models\Accessors\Timestamp;

class Migrator extends Component implements MigratorInterface
{
    /**
     * Migrations repository.
     *
     * @var Repository
     */
    protected $repository = null;

    /**
     * Currently associated database.
     *
     * @var Database
     */
    protected $database = null;

    /**
     * Migrator configuration. Contains information about default table, safe environments and etc.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Core used to resolve environment.
     *
     * @var Core
     */
    protected $core = null;

    /**
     * New migrator instance. Migrator responsible for running migrations and keeping track of what
     * was already executed. Default migrator uses
     *
     * @param Repository $repository
     * @param Database   $database
     * @param array      $config
     * @param Core       $core
     */
    public function __construct(Repository $repository, Database $database, $config, Core $core = null)
    {
        $this->repository = $repository;
        $this->database = $database;
        $this->config = $config;
    }

    /**
     * Associated Database instance.
     *
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Migration table, all migration information will be stored in it.
     *
     * @return Table
     */
    protected function table()
    {
        return $this->database->table($this->config['table']);
    }

    /**
     * Check if current environment is safe to run migrations.
     *
     * @return bool
     */
    public function isSafe()
    {
        if (empty($this->core))
        {
            return false;
        }

        return in_array($this->core->getEnvironment(), $this->config['safeEnvironments']);
    }

    /**
     * Check if migrator are set and can be used. Default migrator will check that migrations table
     * exists in associated database.
     *
     * @return bool
     */
    public function isConfigured()
    {
        return $this->database->hasTable($this->config['table']);
    }

    /**
     * Configure migrator (create tables, files and etc).
     */
    public function configure()
    {
        if (!$this->isConfigured())
        {
            $schema = $this->table()->schema();

            $schema->column('id')->primary();
            $schema->column('migration')->string(255)->index();
            $schema->column('timestamp')->bigInteger();
            $schema->column('timePerformed')->timestamp();

            $schema->save();
        }
    }

    /**
     * Get list of all migrations with their class names, file names, status and migrated time (if
     * presented).
     *
     * @return array
     */
    public function getMigrations()
    {
        $migrations = $this->repository->getMigrations();
        foreach ($migrations as &$migration)
        {
            $dbMigration = $this->table()
                ->where('migration', $migration['name'])
                ->where('timestamp', $migration['timestamp'])
                ->select('id', 'timePerformed as performed')
                ->run()->fetch();

            $migration += $dbMigration ?: ['id' => 0, 'performed' => false];

            if (!empty($migration['performed']))
            {
                $migration['performed'] = Timestamp::castTimestamp(
                    $migration['performed'],
                    DatabaseManager::DEFAULT_TIMEZONE
                );
            }

            unset($migration);
        }

        return $migrations;
    }

    /**
     * Run one outstanding migration, migrations will be performed in an order they were registered.
     */
    public function run()
    {
        $result = null;
        foreach ($this->getMigrations() as $migration)
        {
            if (empty($migration['performed']))
            {
                $instance = $this->repository->getMigration($migration);
                $instance->setDatabase($this->database);
                $instance->up();

                $this->table()->insert([
                    'migration'     => $migration['name'],
                    'timestamp'     => $migration['timestamp'],
                    'timePerformed' => new \DateTime('now')
                ]);

                $result = $migration;
                break;
            }
        }

        return $result;
    }

    /**
     * Rollback last executed migration.
     */
    public function rollback()
    {
        $result = null;
        foreach (array_reverse($this->getMigrations()) as $migration)
        {
            if (!empty($migration['performed']))
            {
                $instance = $this->repository->getMigration($migration);
                $instance->setDatabase($this->database);

                $instance->down();

                $this->table()->delete(['id' => $migration['id']])->run();

                $result = $migration;
                break;
            }
        }

        return $result;
    }
}