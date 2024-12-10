<?php

declare(strict_types=1);

namespace Spiral\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Event\CacheHit;
use Spiral\Cache\Event\CacheMissed;
use Spiral\Cache\Event\CacheRetrieving;
use Spiral\Cache\Event\KeyDeleted;
use Spiral\Cache\Event\KeyDeleteFailed;
use Spiral\Cache\Event\KeyDeleting;
use Spiral\Cache\Event\KeyWriteFailed;
use Spiral\Cache\Event\KeyWriting;
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
        $key = $this->resolveKey($key);

        $this->dispatcher?->dispatch(new CacheRetrieving($key));

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
        $key = $this->resolveKey($key);

        $this->dispatcher?->dispatch(new KeyWriting($key, $value));

        $result = $this->storage->set($key, $value, $ttl);

        $this->dispatcher?->dispatch(
            $result
                ? new KeyWritten($key, $value)
                : new KeyWriteFailed($key, $value),
        );

        return $result;
    }

    public function delete(string $key): bool
    {
        $key = $this->resolveKey($key);

        $this->dispatcher?->dispatch(new KeyDeleting($key));

        $result = $this->storage->delete($key);

        $this->dispatcher?->dispatch(
            $result
                ? new KeyDeleted($key)
                : new KeyDeleteFailed($key),
        );

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
        return $this->prefix . $key;
    }
}
