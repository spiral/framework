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
        protected ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->storage->get($key);

        if ($value === null) {
            $this->dispatcher?->dispatch(new CacheMissed($key));

            return $default;
        }

        $this->dispatcher?->dispatch(new CacheHit($key, $value));

        return $value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $result = $this->storage->set($key, $value, $ttl);

        if ($result) {
            $this->dispatcher?->dispatch(new KeyWritten($key, $value));
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        $result = $this->storage->delete($key);

        if ($result) {
            $this->dispatcher?->dispatch(new KeyDeleted($key));
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
        return $this->storage->has($key);
    }

    public function getStorage(): CacheInterface
    {
        return $this->storage;
    }
}
