<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http\Input;

use Spiral\Helpers\ArrayHelper;

class InputBag implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Associated parameters to read.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Parameter bag used to perform read only operations with request attributes.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Count items.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Check if property key exists.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Get property value.
     *
     * @param string $name    Property key.
     * @param mixed  $default Default value if key not exists.
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!$this->has($name))
        {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * Get all property values.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Fetch only specified keys from property values. Missed values can be filled with defined filler.
     *
     * @param array $keys Keys to fetch from parameter values.
     * @param bool  $fill Fill missing key with filler value.
     * @param mixed $filler
     * @return array
     */
    public function fetch(array $keys, $fill = false, $filler = null)
    {
        $result = ArrayHelper::fetch($this->data, $keys);

        if ($fill)
        {
            $result = $result + array_fill_keys($keys, $filler);
        }

        return $result;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException("InputBag does not allow parameter altering.");
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException("InputBag does not allow parameter altering.");
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return $this->all();
    }
}