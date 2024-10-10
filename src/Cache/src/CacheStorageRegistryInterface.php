<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\SimpleCache\CacheInterface;

interface CacheStorageRegistryInterface
{
    /**
     * @param non-empty-string $name
     */
    public function set(string $name, CacheInterface $cache): void;
}
