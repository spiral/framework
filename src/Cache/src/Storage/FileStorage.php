<?php

declare(strict_types=1);

namespace Spiral\Cache\Storage;

use Psr\SimpleCache\CacheInterface;
use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\FilesInterface;

final class FileStorage implements CacheInterface
{
    use InteractsWithTime;

    /** @var string */
    private $path;

    /** @var FilesInterface */
    private $files;

    /** @var int */
    private $ttl;

    public function __construct(FilesInterface $files, string $path, int $ttl = 2592000)
    {
        $this->path = $path;
        $this->files = $files;
        $this->ttl = $ttl;
    }

    public function get($key, $default = null)
    {
        return $this->getPayload($key)['value'] ?? $default;
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->files->write(
            $this->makePath($key),
            $this->ttlToTimestamp($ttl).serialize($value),
            null,
            true
        );
    }

    public function delete($key)
    {
        if ($this->has($key)) {
            return $this->files->delete($this->makePath($key));
        }

        return false;
    }

    public function clear()
    {
        if (! $this->files->isDirectory($this->path)) {
            return false;
        }

        $this->files->deleteDirectory($this->path);

        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $state = null;

        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl);
            $state = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function deleteMultiple($keys)
    {
        $state = null;
        foreach ($keys as $key) {
            $result = $this->delete($key);
            $state = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function has($key)
    {
        return $this->files->exists($this->makePath($key));
    }

    /**
     * Make the full path for the given cache key.
     */
    protected function makePath(string $key): string
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

        return $this->path.'/'.implode('/', $parts).'/'.$hash;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     */
    protected function getPayload(string $key): array
    {
        $path = $this->makePath($key);

        try {
            $expire = (int) substr(
                $contents = $this->files->read($path),
                0,
                10
            );
        } catch (FileNotFoundException $e) {
            return $this->makeEmptyPayload();
        }

        if (time() >= $expire) {
            $this->delete($key);

            return $this->makeEmptyPayload();
        }

        try {
            $data = unserialize(substr($contents, 10));
        } catch (\Exception $e) {
            $this->delete($key);

            return $this->makeEmptyPayload();
        }

        $time = $expire - time();

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
