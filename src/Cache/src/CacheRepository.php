<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Event\CacheHit;
use Spiral\Cache\Event\CacheMissed;
use Spiral\Cache\Event\KeyDeleted;
use Spiral\Cache\Event\KeyWritten;

/**
 * @internal
 */
class CacheRepository implements CacheInterface
{
    public function __construct(
        protected CacheInterface $storage,
        protected ?EventDispatcherInterface $dispatcher = null,
        protected ?string $prefix = null
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->storage->get($this->resolveKey($key));

        if ($value === null) {
            $this->dispatcher?->dispatch(new CacheMissed($this->resolveKey($key)));

            return $default;
        }

        $this->dispatcher?->dispatch(new CacheHit($this->resolveKey($key), $value));

        return $value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $result = $this->storage->set($this->resolveKey($key), $value, $ttl);

        if ($result) {
            $this->dispatcher?->dispatch(new KeyWritten($this->resolveKey($key), $value));
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        $result = $this->storage->delete($this->resolveKey($key));

        if ($result) {
            $this->dispatcher?->dispatch(new KeyDeleted($this->resolveKey($key)));
        }

        return $result;
    }

    public function clear(): bool
    {
        return $this->storage->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $state = null;

        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl);
            $state = \is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;
        foreach ($keys as $key) {
            $result = $this->delete($key);
            $state = \is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function has(string $key): bool
    {
        return $this->storage->has($this->resolveKey($key));
    }

    public function getStorage(): CacheInterface
    {
        return $this->storage;
    }

    private function resolveKey(string $key): string
    {
        if (!empty($this->prefix)) {
            return $this->prefix . $key;
        }

        return $key;
    }
}
