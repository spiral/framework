<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Request;

use Spiral\Http\Exception\DotNotFoundException;
use Spiral\Http\Exception\InputException;

/**
 * Generic data accessor, used to read properties of active request. Input bags provide ability to
 * isolate request parts using given prefix.
 */
class InputBag implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /** @var array */
    private $data = [];

    /** @var string */
    private $prefix = '';

    public function __construct(array $data, string $prefix = '')
    {
        $this->data = $data;
        $this->prefix = $prefix;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->all();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->all());
    }

    public function all(): array
    {
        try {
            return $this->dotGet('');
        } catch (DotNotFoundException $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Fetch only specified keys from property values. Missed values can be filled with defined
     * filler. Only one variable layer can be fetched (no dot notation).
     *
     * @param bool  $fill Fill missing key with filler value.
     * @param mixed $filler
     * @return array
     */
    public function fetch(array $keys, bool $fill = false, $filler = null)
    {
        $result = array_intersect_key($this->all(), array_flip($keys));
        ;
        if (!$fill) {
            return $result;
        }

        return $result + array_fill_keys($keys, $filler);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Check if field presented (can be empty) by it's name. Dot notation allowed.
     */
    public function has(string $name): bool
    {
        try {
            $this->dotGet($name);
        } catch (DotNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Get property or return default value. Dot notation allowed.
     *
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        try {
            return $this->dotGet($name);
        } catch (DotNotFoundException $e) {
            return $default;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InputException
     */
    public function offsetSet($offset, $value): void
    {
        throw new InputException('InputBag is immutable');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InputException
     */
    public function offsetUnset($offset): void
    {
        throw new InputException('InputBag is immutable');
    }

    /**
     * Get element using dot notation.
     *
     * @return mixed|null
     * @throws DotNotFoundException
     */
    private function dotGet(string $name)
    {
        $data = $this->data;

        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = explode('.', rtrim($path, '.'));

        foreach ($path as $step) {
            if (!is_array($data) || !array_key_exists($step, $data)) {
                throw new DotNotFoundException("Unable to find requested element '{$name}'");
            }

            $data = &$data[$step];
        }

        return $data;
    }
}
