<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

class CacheManager implements CacheStorageProviderInterface, SingletonInterface
{
    /** @var CacheConfig */
    private $config;

    /** @var CacheInterface[] */
    private $storages = [];

    /** @var FactoryInterface */
    private $factory;

    public function __construct(CacheConfig $config, FactoryInterface $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    public function storage(?string $name = null): CacheInterface
    {
        $name = $name ?: $this->config->getDefaultStorage();

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

        return $this->factory->make($config['type'], $config);
    }
}
