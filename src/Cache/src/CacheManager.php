<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\FactoryInterface;

#[Singleton]
class CacheManager implements CacheStorageProviderInterface
{
    /** @var CacheInterface[] */
    private array $storages = [];

    public function __construct(
        private readonly CacheConfig $config,
        private readonly FactoryInterface $factory,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    public function storage(?string $name = null): CacheInterface
    {
        $name ??= $this->config->getDefaultStorage();

        // Replaces alias with real storage name
        $storage = $this->config->getAliases()[$name] ?? $name;

        $prefix = null;
        if (\is_array($storage)) {
            $prefix = !empty($storage['prefix']) ? $storage['prefix'] : null;
            $storage = $storage['storage'];
        }

        if (!isset($this->storages[$storage])) {
            $this->storages[$storage] = $this->resolve($storage);
        }

        return new CacheRepository($this->storages[$storage], $this->dispatcher, $prefix);
    }

    private function resolve(?string $name): CacheInterface
    {
        $config = $this->config->getStorageConfig($name);

        return $this->factory->make($config['type'], $config);
    }
}
