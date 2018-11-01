<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Database;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Migrations\FileRepository;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\RepositoryInterface;

class MigrationsBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        Migrator::class            => Migrator::class,
        RepositoryInterface::class => FileRepository::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param EnvironmentInterface  $environment
     * @param DirectoriesInterface  $directories
     */
    public function boot(
        ConfiguratorInterface $configurator,
        EnvironmentInterface $environment,
        DirectoriesInterface $directories
    ) {
        if (!$directories->has('migrations')) {
            $directories->set('migrations', $directories->get('app') . 'migrations');
        }

        $configurator->setDefaults('migration', [
            'directory' => $directories->get('migrations'),
            'table'     => 'migrations',
            'safe'      => $environment->get('DEBUG', false)
        ]);
    }
}