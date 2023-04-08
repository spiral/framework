<?php

declare(strict_types=1);

namespace Spiral\Http\Request;

use Spiral\Http\Exception\DotNotFoundException;
use Spiral\Http\Exception\InputException;

/**
 * Generic data accessor, used to read properties of active request. Input bags provide ability to
 * isolate request parts using given prefix.
 *
 * @implements \IteratorAggregate<mixed, mixed>
 * @implements \ArrayAccess<mixed, mixed>
 */
class InputBag implements \Countable, \IteratorAggregate, \ArrayAccess
{
    public function __construct(
        private readonly array $data,
        private readonly int|string $prefix = ''
    ) {
    }

    public function __debugInfo(): array
    {
        return $this->all();
    }

    public function count(): int
    {
        return \count($this->all());
    }

    public function all(): array
    {
        try {
            return $this->dotGet('');
        } catch (DotNotFoundException) {
            return [];
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Fetch only specified keys from property values. Missed values can be filled with defined
     * filler. Only one variable layer can be fetched (no dot notation).
     *
     * @param bool $fill Fill missing key with filler value.
     */
    public function fetch(array $keys, bool $fill = false, mixed $filler = null): array
    {
        $result = \array_intersect_key($this->all(), \array_flip($keys));
        if (!$fill) {
            return $result;
        }

        return $result + \array_fill_keys($keys, $filler);
    }

    /**
     * Check if field presented (can be empty) by it's name. Dot notation allowed.
     */
    public function has(int|string $name): bool
    {
        try {
            $this->dotGet($name);
        } catch (DotNotFoundException) {
            return false;
        }

        return true;
    }

    public function offsetExists(mixed $offset): bool
    {
        try {
            return !\is_null($this->dotGet($offset));
        } catch (DotNotFoundException) {
            return false;
        }
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Get property or return default value. Dot notation allowed.
     */
    public function get(int|string $name, mixed $default = null): mixed
    {
        try {
            return $this->dotGet($name);
        } catch (DotNotFoundException) {
            return $default;
        }
    }

    /**
     * @throws InputException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new InputException('InputBag is immutable.');
    }

    /**
     * @throws InputException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new InputException('InputBag is immutable.');
    }

    /**
     * Get element using dot notation.
     *
     * @throws DotNotFoundException
     */
    private function dotGet(int|string $name): mixed
    {
        $data = $this->data;

        //Generating path relative to a given name and prefix
        $path = (empty($this->prefix) ? '' : $this->prefix . '.') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = \explode('.', \rtrim($path, '.'));

        foreach ($path as $step) {
            if (!\is_array($data) || !\array_key_exists($step, $data)) {
                throw new DotNotFoundException(\sprintf("Unable to find requested element '%s'.", $name));
            }

            $data = &$data[$step];
        }

        return $data;
    }
}
