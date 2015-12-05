<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Input\Bags;

use Spiral\Http\Exceptions\DotNotFoundException;
use Spiral\Http\Exceptions\Request\InputException;

/**
 * Generic data accessor, used to read properties of active request.
 */
class InputBag implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Check if field presented (can be empty) by it's name. Dot notation allowed.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        try {
            $this->dotGet($name);
        } catch (DotNotFoundException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Get property or return default value. Dot notation allowed.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        try {
            return $this->dotGet($name);
        } catch (DotNotFoundException $exception) {
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
     * @return array
     */
    public function fetch(array $keys, $fill = false, $filler = null)
    {
        $result = array_intersect_key($this->data, array_flip($keys));;

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
     * @return mixed|null
     */
    private function dotGet($name)
    {
        $data = $this->data;

        $path = explode('.', $name);
        foreach ($path as $step) {
            if (!is_array($data) || !array_key_exists($step, $data)) {
                throw new DotNotFoundException("Unable to find requested element '{$name}'.");
            }
            $data = &$data[$step];
        }

        return $data;
    }
}