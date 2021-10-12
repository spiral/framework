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
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\TokenizerBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Migrations\FileRepository;
use Cycle\Migrations\FileRepository as CycleFileRepository;
use Spiral\Migrations\Migrator;
use Cycle\Migrations\Migrator as CycleMigrator;
use Spiral\Migrations\MigratorInterface;
use Cycle\Migrations\MigratorInterface as CycleMigratorInterface;
use Spiral\Migrations\RepositoryInterface;
use Cycle\Migrations\RepositoryInterface as CycleRepositoryInterface;

final class MigrationsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
        DatabaseBootloader::class,
    ];

    /**
     * @var ConfiguratorInterface
     */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param EnvironmentInterface $env
     * @param DirectoriesInterface $dirs
     * @param Container $container
     * @return void
     */
    public function boot(EnvironmentInterface $env, DirectoriesInterface $dirs, Container $container): void
    {
        if (!$dirs->has('migrations')) {
            $dirs->set('migrations', $dirs->get('app') . 'migrations');
        }

        $this->bootConfigs($env, $dirs);

        $container->bindSingleton(MigratorInterface::class, Migrator::class);
        $container->bindSingleton(RepositoryInterface::class, FileRepository::class);

        if (\class_exists(CycleMigrator::class)) {
            $container->bindSingleton(
                CycleMigratorInterface::class,
                static function (MigratorInterface $migrator) {
                    return $migrator;
                }
            );

            $container->bindSingleton(
                CycleMigrator::class,
                static function (Migrator $migrator) {
                    return $migrator;
                }
            );
        }

        if (\class_exists(CycleFileRepository::class)) {
            $container->bindSingleton(
                CycleFileRepository::class,
                static function (FileRepository $repository) {
                    return $repository;
                }
            );

            $container->bindSingleton(
                CycleRepositoryInterface::class,
                static function (RepositoryInterface $repository) {
                    return $repository;
                }
            );
        }
    }

    /**
     * @param EnvironmentInterface $env
     * @param DirectoriesInterface $dirs
     * @return void
     */
    private function bootConfigs(EnvironmentInterface $env, DirectoriesInterface $dirs): void
    {
        $this->config->setDefaults('migration', [
            'directory' => $dirs->get('migrations'),
            'table'     => 'migrations',
            'safe'      => $env->get('SAFE_MIGRATIONS', false),
        ]);
    }
}
