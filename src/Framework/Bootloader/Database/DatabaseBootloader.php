<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Database;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\Database as SpiralDatabase;
use Cycle\Database\Database as CycleDatabase;
use Spiral\Database\DatabaseInterface as SpiralDatabaseInterface;
use Cycle\Database\DatabaseInterface as CycleDatabaseInterface;
use Spiral\Database\DatabaseManager as SpiralDatabaseManager;
use Cycle\Database\DatabaseManager as CycleDatabaseManager;
use Spiral\Database\DatabaseProviderInterface as SpiralDatabaseProviderInterface;
use Cycle\Database\DatabaseProviderInterface as CycleDatabaseProviderInterface;

final class DatabaseBootloader extends Bootloader implements SingletonInterface
{
    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Init database config.
     *
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        $this->bootConfigs();

        $container->bindSingleton(SpiralDatabaseProviderInterface::class, SpiralDatabaseManager::class);
        $container->bind(SpiralDatabaseInterface::class, SpiralDatabase::class);

        if (\class_exists(CycleDatabaseManager::class)) {
            $container->bindSingleton(
                CycleDatabaseProviderInterface::class,
                static function (SpiralDatabaseProviderInterface $manager) {
                    return $manager;
                }
            );

            $container->bindSingleton(
                CycleDatabaseManager::class,
                static function (SpiralDatabaseManager $manager) {
                    return $manager;
                }
            );
        }

        if (\class_exists(CycleDatabase::class)) {
            $container->bind(
                CycleDatabaseInterface::class,
                static function (SpiralDatabaseInterface $database) {
                    return $database;
                }
            );

            $container->bind(
                CycleDatabase::class,
                static function (SpiralDatabaseInterface $database) {
                    return $database;
                }
            );
        }
    }

    /**
     * @return void
     */
    private function bootConfigs(): void
    {
        $this->config->setDefaults('database', [
            'default'   => 'default',
            'aliases'   => [],
            'databases' => [],
            'drivers'   => [],
        ]);
    }
}
