<?php

declare(strict_types=1);

namespace Spiral\Session\Handler;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\Cache\CacheStorageProviderInterface;

final class CacheHandler implements \SessionHandlerInterface
{
    private readonly CacheInterface $cache;

    public function __construct(
        CacheStorageProviderInterface $storageProvider,
        private readonly ?string $storage = null,
        private readonly int $ttl = 86400
    ) {
        $this->cache = $storageProvider->storage($this->storage);
    }

    public function close(): bool
    {
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function destroy(string $id): bool
    {
        $this->cache->delete($id);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function read(string $id): string|false
    {
        $result = $this->cache->get($id);

        return is_string($result) ? $result : '';
    }

    /**
     * @throws InvalidArgumentException
     */
    public function write(string $id, string $data): bool
    {
        return $this->cache->set($this->getKey($id), $data, $this->ttl);
    }

    private function getKey(string $id): string
    {
        return 'session:' . $id;
    }
}
