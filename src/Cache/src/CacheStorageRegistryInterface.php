<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Use this interface to register cache storages at the bootloading stage.
 */
interface CacheStorageRegistryInterface
{
    /**
     * @param non-empty-string $name
     */
    public function register(string $name, CacheInterface $cache): void;

    /**
     * @return array<non-empty-string, CacheInterface>
     */
    public function getCacheStorages(): array;
}
