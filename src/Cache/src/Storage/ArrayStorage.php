<?php

declare(strict_types=1);

namespace Spiral\Cache\Storage;

use Psr\SimpleCache\CacheInterface;

class ArrayStorage implements CacheInterface
{
    use InteractsWithTime;

    /**
     * The array of stored values.
     *
     * @var array
     */
    protected $storage = [];

    /** @var int */
    private $ttl;

    public function __construct(int $ttl = 2592000)
    {
        $this->ttl = $ttl;
    }

    public function get($key, $default = null)
    {
        if (! isset($this->storage[$key])) {
            return $default;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['timestamp'] ?? 0;

        if ($expiresAt !== 0 && time() >= $expiresAt) {
            $this->delete($key);

            return $default;
        }

        return $item['value'];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->storage[$key] = [
            'value' => $value,
            'timestamp' => $this->ttlToTimestamp($ttl),
        ];

        return true;
    }

    public function delete($key): bool
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    public function clear(): bool
    {
        $this->storage = [];

        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $this->get($key, $default);
        }

        return $return;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return false;
    }

    public function deleteMultiple($keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);

            $state = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function has($key): bool
    {
        return \array_key_exists($key, $this->storage);
    }
}
