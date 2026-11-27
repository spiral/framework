<?php

declare(strict_types=1);

namespace Spiral\Http\Request;

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
    /**
     * What the prefix points to, resolved on the first read: whether it exists, and the value.
     *
     * @var array{0: bool, 1: mixed}|null
     */
    private ?array $scope = null;

    public function __construct(
        private readonly array $data,
        private readonly int|string $prefix = '',
    ) {}

    public function count(): int
    {
        return \count($this->all());
    }

    public function all(): array
    {
        $value = $this->dotGet('', $found);

        return $found ? $value : [];
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
        $this->dotGet($name, $found);

        return $found;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->dotGet($offset) !== null;
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
        $value = $this->dotGet($name, $found);

        return $found ? $value : $default;
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

    public function __debugInfo(): array
    {
        return $this->all();
    }

    /**
     * @param list<string> $steps
     * @param-out bool $found Set to false when a step is missing, left as is otherwise.
     */
    private static function walk(mixed $data, array $steps, bool &$found): mixed
    {
        foreach ($steps as $step) {
            if (!\is_array($data) || !\array_key_exists($step, $data)) {
                $found = false;

                return null;
            }

            // By value: taking a reference would separate, and so copy, every array on the path.
            $data = $data[$step];
        }

        return $data;
    }

    /**
     * Get element using dot notation.
     *
     * A missing element is reported through $found and not by an exception: filters probe every
     * field this way, and an exception costs a backtrace of the whole nested filter stack.
     *
     * @param-out bool $found False when there is no such element; null is a valid value of a found one.
     */
    private function dotGet(int|string $name, ?bool &$found = null): mixed
    {
        $found = true;
        $path = (string) $name;

        if (empty($this->prefix)) {
            return empty($path)
                ? $this->data
                : self::walk($this->data, \explode('.', \rtrim($path, '.')), $found);
        }

        $path = \rtrim($path, '.');
        $prefix = (string) $this->prefix;

        // With nothing to read below the prefix, its own trailing dots are trimmed too, so the
        // cached scope, which keeps them as steps, does not apply.
        if ($path === '' && \str_ends_with($prefix, '.')) {
            return self::walk($this->data, \explode('.', \rtrim($prefix, '.')), $found);
        }

        if ($this->scope === null) {
            $exists = true;
            $scope = self::walk($this->data, \explode('.', $prefix), $exists);
            $this->scope = [$exists, $scope];
        }

        [$found, $data] = $this->scope;

        return $found && $path !== ''
            ? self::walk($data, \explode('.', $path), $found)
            : $data;
    }
}
