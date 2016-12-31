<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Configs;

use Spiral\Core\InjectableConfig;

class MigrationsConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'migrations';

    /**
     * @var array
     */
    protected $config = [
        'directory' => '',
        'database'  => 'default',
        'table'     => 'migrations',
        'safe'      => false
    ];

    /**
     * Migrations directory.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->config['directory'];
    }

    /**
     * Database to store information about executed migration.
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->config['database'];
    }

    /**
     * Table to store list of executed migrations.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->config['table'];
    }

    /**
     * Is it safe to run migration without user confirmation?
     *
     * @return bool
     */
    public function isSafe(): bool
    {
        return $this->config['safe'];
    }
}