<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Traversable;

final class ArrayCache implements CacheInterface
{
    protected const EXPIRATION_INFINITY = 0;
    protected const EXPIRATION_EXPIRED = -1;

    public bool $returnOnSet = true;
    public bool $returnOnDelete = true;
    public bool $returnOnClear = true;

    /** @var array<string, array<int, mixed>> */
    protected array $cache = [];

    public function __construct(array $cacheData = [])
    {
        $this->setMultiple($cacheData);
    }

    public function get($key, $default = null)
    {
        $this->validateKey($key);
        if (\array_key_exists($key, $this->cache) && !$this->isExpired($key)) {
            /** @psalm-var mixed $value */
            $value = $this->cache[$key][0];
            if (\is_object($value)) {
                $value = clone $value;
            }

            return $value;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);
        $expiration = $this->ttlToExpiration($ttl);
        if ($expiration < 0) {
            return $this->delete($key);
        }
        if (\is_object($value)) {
            $value = clone $value;
        }
        $this->cache[$key] = [$value, $expiration];
        return $this->returnOnSet;
    }

    public function delete($key): bool
    {
        $this->validateKey($key);
        unset($this->cache[$key]);
        return $this->returnOnDelete;
    }

    public function clear(): bool
    {
        $this->cache = [];
        return $this->returnOnClear;
    }

    /**
     * @param iterable $keys
     * @param mixed $default
     *
     * @return mixed[]
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        /** @psalm-var string[] $keys */
        $result = [];
        foreach ($keys as $key) {
            /** @psalm-var mixed */
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @param iterable $values
     * @param DateInterval|int|null $ttl
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        $this->validateKeysOfValues($values);
        /**
         * @psalm-var mixed $value
         */
        foreach ($values as $key => $value) {
            $this->set((string)$key, $value, $ttl);
        }
        return $this->returnOnSet;
    }

    public function deleteMultiple($keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        /** @var string[] $keys */
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return $this->returnOnDelete;
    }

    public function has($key): bool
    {
        $this->validateKey($key);
        /** @psalm-var string $key */
        return isset($this->cache[$key]) && !$this->isExpired($key);
    }

    /**
     * Get stored data
     *
     * @return array<array-key, mixed>
     */
    public function getValues(): array
    {
        $result = [];
        foreach ($this->cache as $key => $value) {
            /** @psalm-var mixed */
            $result[$key] = $value[0];
        }
        return $result;
    }

    /**
     * Checks whether item is expired or not
     */
    private function isExpired(string $key): bool
    {
        return $this->cache[$key][1] !== 0 && $this->cache[$key][1] <= \time();
    }

    /**
     * Converts TTL to expiration
     *
     * @param DateInterval|int|null $ttl
     *
     * @return int
     */
    private function ttlToExpiration($ttl): int
    {
        $ttl = $this->normalizeTtl($ttl);

        if ($ttl === null) {
            $expiration = self::EXPIRATION_INFINITY;
        } elseif ($ttl <= 0) {
            $expiration = self::EXPIRATION_EXPIRED;
        } else {
            $expiration = $ttl + time();
        }

        return $expiration;
    }

    /**
     * Normalizes cache TTL handling strings and {@see DateInterval} objects.
     *
     * @param DateInterval|int|string|null $ttl raw TTL.
     *
     * @return int|null TTL value as UNIX timestamp or null meaning infinity
     */
    private function normalizeTtl($ttl): ?int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))->add($ttl)->getTimestamp();
        }

        if (\is_string($ttl)) {
            return (int)$ttl;
        }

        return $ttl;
    }

    /**
     * @param mixed $iterable
     *
     * Converts iterable to array. If provided value is not iterable it throws an InvalidArgumentException
     */
    private function iterableToArray($iterable): array
    {
        if (!is_iterable($iterable)) {
            throw new \InvalidArgumentException(\sprintf('Iterable is expected, got %s.', \gettype($iterable)));
        }
        return $iterable instanceof Traversable ? \iterator_to_array($iterable) : $iterable;
    }

    /**
     * @param mixed $key
     */
    private function validateKey($key): void
    {
        if (!\is_string($key) || $key === '' || \strpbrk($key, '{}()/\@:')) {
            throw new \InvalidArgumentException('Invalid key value.');
        }
    }

    /**
     * @param mixed[] $keys
     */
    private function validateKeys(array $keys): void
    {
        /** @psalm-var mixed $key */
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    private function validateKeysOfValues(array $values): void
    {
        $keys = \array_map('strval', \array_keys($values));
        $this->validateKeys($keys);
    }
}
