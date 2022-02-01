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
use Spiral\Config\ConfiguratorInterface;
use Spiral\Migrations\FileRepository;
use Spiral\Migrations\Migrator;
use Spiral\Migrations\RepositoryInterface;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class MigrationsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
        DatabaseBootloader::class,
    ];

    protected const SINGLETONS = [
        Migrator::class            => Migrator::class,
        RepositoryInterface::class => FileRepository::class,
    ];

    /**
     * @param ConfiguratorInterface $config
     * @param EnvironmentInterface  $env
     * @param DirectoriesInterface  $dirs
     */
    public function boot(
        ConfiguratorInterface $config,
        EnvironmentInterface $env,
        DirectoriesInterface $dirs
    ): void {
        if (!$dirs->has('migrations')) {
            $dirs->set('migrations', $dirs->get('app') . 'migrations');
        }

        $config->setDefaults(
            'migration',
            [
                'directory' => $dirs->get('migrations'),
                'table'     => 'migrations',
                'safe'      => $env->get('SAFE_MIGRATIONS', false),
            ]
        );
    }
}
