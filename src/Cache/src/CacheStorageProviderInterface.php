<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Exception\StorageException;

interface CacheStorageProviderInterface
{
    /**
     * Get a cache storage instance by name.
     *
     * @throws StorageException
     */
    public function storage(?string $name = null): CacheInterface;
}
