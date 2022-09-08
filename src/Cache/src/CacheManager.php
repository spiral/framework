<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

class CacheManager implements CacheStorageProviderInterface, SingletonInterface
{
    /** @var CacheInterface[] */
    private array $storages = [];

    public function __construct(
        private readonly CacheConfig $config,
        private readonly FactoryInterface $factory
    ) {
    }

    public function storage(?string $name = null): CacheInterface
    {
        $name ??= $this->config->getDefaultStorage();

        // Replaces alias with real storage name
        $name = $this->config->getAliases()[$name] ?? $name;

        if (isset($this->storages[$name])) {
            return $this->storages[$name];
        }

        return $this->storages[$name] = $this->resolve($name);
    }

    private function resolve(?string $name): CacheInterface
    {
        $config = $this->config->getStorageConfig($name);

        return new CacheRepository($this->factory->make($config['type'], $config));
    }
}
