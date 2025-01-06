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
        protected ?string $prefix = null,
    ) {}

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
        $array = [];
        // Resolve keys and dispatch events
        foreach ($keys as $key) {
            $key = $this->resolveKey($key);
            $this->dispatcher?->dispatch(new CacheRetrieving($key));
            // Fill resulting array with default values
            $array[$key] = $default;
        }

        // If no dispatcher is set, we can skip the loop with events
        // to save some CPU cycles
        $keys = \array_keys($array);
        if ($this->dispatcher === null) {
            return $this->storage->getMultiple($keys, $default);
        }

        $result = $this->storage->getMultiple($keys);

        foreach ($result as $key => $value) {
            if ($value === null) {
                $this->dispatcher->dispatch(new CacheMissed($key));
            } else {
                // Replace default value with actual value in the resulting array
                $array[$key] = $value;
                $this->dispatcher->dispatch(new CacheHit($key, $value));
            }
        }

        return $array;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $dispatcher = $this->dispatcher;
        $array = [];
        // Resolve keys and dispatch events
        foreach ($values as $key => $value) {
            $key = $this->resolveKey($key);
            $dispatcher?->dispatch(new KeyWriting($key, $value));
            $array[$key] = $value;
        }

        $result = $this->storage->setMultiple($array, $ttl);

        // If there is a dispatcher, we need to dispatch events for each key
        $dispatcher === null or \array_walk(
            $array,
            $result
                ? static fn(mixed $value, string $key) => $dispatcher->dispatch(new KeyWritten($key, $value))
                : static fn(mixed $value, string $key) => $dispatcher->dispatch(new KeyWriteFailed($key, $value)),
        );

        return $result;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $dispatcher = $this->dispatcher;

        $array = [];
        // Resolve keys and dispatch events
        foreach ($keys as $key) {
            $key = $this->resolveKey($key);
            $dispatcher?->dispatch(new KeyDeleting($key));
            $array[] = $key;
        }

        $result = $this->storage->deleteMultiple($array);

        // If there is a dispatcher, we need to dispatch events for each key
        $dispatcher === null or \array_walk(
            $array,
            $result
                ? static fn(string $key) => $dispatcher->dispatch(new KeyDeleted($key))
                : static fn(string $key) => $dispatcher->dispatch(new KeyDeleteFailed($key)),
        );

        return $result;
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
