<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

/**
 * Represents part of _SESSION array.
 */
class SessionSegment implements SegmentInterface
{
    /**
     * Reference to _SESSION segment.
     *
     * @var array
     */
    private $segment;

    /**
     * @param array $segment
     */
    public function __construct(array &$segment)
    {
        $this->segment = $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->segment);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value)
    {
        $this->segment[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->segment);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        if (!$this->has($name)) {
            return $default;
        }

        return $this->segment[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function pull(string $name, $default = null)
    {
        $value = $this->get($name, $default);
        $this->delete($name);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $name)
    {
        unset($this->segment[$name]);
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
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }
}