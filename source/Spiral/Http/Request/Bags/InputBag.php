<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Request\Bags;

use Spiral\Http\Exceptions\DotNotFoundException;
use Spiral\Http\Exceptions\InputException;

/**
 * Generic data accessor, used to read properties of active request. Input bags provide ability to
 * isolate request parts using given prefix.
 */
class InputBag implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Data prefix.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * @param array $data
     */
    public function __construct(array $data, string $prefix = '')
    {
        $this->data = $data;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * @return array
     */
    public function all(): array
    {
        try {
            return $this->dotGet('');
        } catch (DotNotFoundException $e) {
            return [];
        }
    }

    /**
     * Check if field presented (can be empty) by it's name. Dot notation allowed.
     *
     * @param string $name
     *
     * @return bool
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
     * Get property or return default value. Dot notation allowed.
     *
     * @param string $name
     * @param mixed  $default
     *
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
     * Fetch only specified keys from property values. Missed values can be filled with defined
     * filler. Only one variable layer can be fetched (no dot notation).
     *
     * @param array $keys
     * @param bool  $fill Fill missing key with filler value.
     * @param mixed $filler
     *
     * @return array
     */
    public function fetch(array $keys, bool $fill = false, $filler = null)
    {
        $result = array_intersect_key($this->all(), array_flip($keys));;

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
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InputException
     */
    public function offsetSet($offset, $value)
    {
        throw new InputException("InputBag does not allow parameter altering.");
    }

    /**
     * {@inheritdoc}
     *
     * @throws InputException
     */
    public function offsetUnset($offset)
    {
        throw new InputException("InputBag does not allow parameter altering.");
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->all();
    }

    /**
     * Get element using dot notation.
     *
     * @param string $name
     *
     * @return mixed|null
     *
     * @throws DotNotFoundException
     */
    private function dotGet(string $name)
    {
        $data = $this->data;

        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
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