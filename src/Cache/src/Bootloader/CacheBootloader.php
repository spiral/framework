<?php

declare(strict_types=1);

namespace Spiral\Cache\Bootloader;

use Psr\SimpleCache\CacheInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cache\CacheManager;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container;

final class CacheBootloader extends Bootloader
{
    protected const SINGLETONS = [
        CacheStorageProviderInterface::class => CacheManager::class,
        CacheManager::class => [self::class, 'initCacheManager'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function registerTypeAlias(string $storageClass, string $alias): void
    {
        $this->config->modify(
            CacheConfig::CONFIG,
            new Append('typeAliases', $alias, $storageClass)
        );
    }

    public function boot(Container $container, EnvironmentInterface $env, DirectoriesInterface $dirs): void
    {
        $this->initConfig($env, $dirs);

        $container->bind(CacheInterface::class, fn(CacheManager $manager) => $manager->storage());
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initCacheManager(Container $container, CacheConfig $config): CacheManager
    {
        $manager = new CacheManager($config, $container);

        foreach ($config->getAliases() as $alias => $storageName) {
            $container->bind($alias, static fn(CacheManager $manager) => $manager->storage($storageName));
        }

        return $manager;
    }

    private function initConfig(EnvironmentInterface $env, DirectoriesInterface $dirs): void
    {
        $this->config->setDefaults(
            CacheConfig::CONFIG,
            [
                'default' => $env->get('CACHE_STORAGE', 'array'),
                'aliases' => [],
                'storages' => [
                    'array' => [
                        'type' => 'array',
                    ],
                    'file' => [
                        'type' => 'file',
                        'path' => $dirs->get('runtime') . 'cache',
                    ],
                ],
                'typeAliases' => [
                    'array' => ArrayStorage::class,
                    'file' => FileStorage::class,
                ],
            ]
        );
    }
}
