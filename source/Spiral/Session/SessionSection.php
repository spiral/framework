<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

use Spiral\Core\Container\InjectableInterface;

/**
 * Represents part of _SESSION array.
 */
class SessionSection implements SessionSectionInterface, InjectableInterface
{
    /**
     * Automatic injections.
     */
    const INJECTOR = SessionInterface::class;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * Reference to _SESSION segment.
     *
     * @var array
     */
    private $name;

    /**
     * @param \Spiral\Session\SessionInterface $session
     * @param string                           $name
     */
    public function __construct(SessionInterface $session, string $name = null)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        $this->resumeSection();

        return $_SESSION[$this->name];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value)
    {
        $this->resumeSection();

        $_SESSION[$this->name][$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name)
    {
        $this->resumeSection();

        return array_key_exists($name, $_SESSION[$this->name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        if (!$this->has($name)) {
            return $default;
        }

        return $_SESSION[$this->name][$name];
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
        $this->resumeSection();
        unset($_SESSION[$this->name][$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->resumeSection();
        $_SESSION[$this->name] = [];
    }

    /**
     * Shortcut for get.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     */
    public function __unset(string $name)
    {
        $this->delete($name);
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

    /**
     * Ensure that session have proper section.
     */
    private function resumeSection()
    {
        $this->session->resume();

        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
    }
}