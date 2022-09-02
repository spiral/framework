<?php

declare(strict_types=1);

namespace Spiral\Cache\Storage;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Event\CacheHit;
use Spiral\Cache\Event\CacheMissed;
use Spiral\Cache\Event\KeyDeleted;
use Spiral\Cache\Event\KeyWritten;
use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\FilesInterface;

final class FileStorage implements CacheInterface
{
    use InteractsWithTime;

    public function __construct(
        private readonly FilesInterface $files,
        private readonly string $path,
        private readonly int $ttl = 2_592_000,
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $payload = $this->getPayload($key)['value'];

        if ($payload === null) {
            $this->dispatcher?->dispatch(new CacheMissed($key));

            return $default;
        }

        $this->dispatcher?->dispatch(new CacheHit($key, $payload));

        return $payload;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval|\DateTimeInterface $ttl = null): bool
    {
        $result = $this->files->write(
            $this->makePath($key),
            $this->ttlToTimestamp($ttl) . \serialize($value),
            null,
            true
        );

        if ($result) {
            $this->dispatcher?->dispatch(new KeyWritten($key, $value));
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            $result = $this->files->delete($this->makePath($key));

            if ($result) {
                $this->dispatcher?->dispatch(new KeyDeleted($key));
            }

            return $result;
        }

        return false;
    }

    public function clear(): bool
    {
        if (!$this->files->isDirectory($this->path)) {
            return false;
        }

        $this->files->deleteDirectory($this->path);

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval|\DateTimeInterface $ttl = null): bool
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
        return $this->files->exists($this->makePath($key));
    }

    /**
     * Make the full path for the given cache key.
     */
    protected function makePath(string $key): string
    {
        $parts = \array_slice(\str_split($hash = \sha1($key), 2), 0, 2);

        return $this->path . '/' . \implode('/', $parts) . '/' . $hash;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     */
    protected function getPayload(string $key): array
    {
        $path = $this->makePath($key);

        try {
            $expire = (int) \substr(
                $contents = $this->files->read($path),
                0,
                10
            );
        } catch (FileNotFoundException) {
            return $this->makeEmptyPayload();
        }

        if (\time() >= $expire) {
            $this->delete($key);

            return $this->makeEmptyPayload();
        }

        try {
            $data = \unserialize(\substr($contents, 10));
        } catch (\Exception) {
            $this->delete($key);

            return $this->makeEmptyPayload();
        }

        $time = $expire - \time();

        return ['value' => $data, 'timestamp' => $time];
    }

    /**
     * Make a default empty payload for the cache.
     */
    protected function makeEmptyPayload(): array
    {
        return ['value' => null, 'timestamp' => null];
    }
}
