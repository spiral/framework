<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Core\Container\InjectableInterface;

/**
 * Represents part of _SESSION array.
 */
final class SessionSection implements SessionSectionInterface, InjectableInterface
{
    private SessionInterface $session;

    /**
     * Reference to _SESSION segment.
     *
     * @var array
     */
    private $name;

    /**
     * @param string|null      $name
     */
    public function __construct(SessionInterface $session, string $name = null)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * Shortcut for get.
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param mixed  $value
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @return bool
     */
    public function __isset(string $name)
    {
        return $this->has($name);
    }

    public function __unset(string $name): void
    {
        $this->delete($name);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getAll());
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        $this->resumeSection();

        return $_SESSION[$this->name];
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, $value): void
    {
        $this->resumeSection();

        $_SESSION[$this->name][$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function has(string $name): bool
    {
        $this->resumeSection();

        return array_key_exists($name, $_SESSION[$this->name]);
    }

    /**
     * @inheritdoc
     */
    public function get(string $name, $default = null)
    {
        if (!$this->has($name)) {
            return $default;
        }

        return $_SESSION[$this->name][$name];
    }

    /**
     * @inheritdoc
     */
    public function pull(string $name, $default = null)
    {
        $value = $this->get($name, $default);
        $this->delete($name);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $name): void
    {
        $this->resumeSection();
        unset($_SESSION[$this->name][$name]);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->resumeSection();
        $_SESSION[$this->name] = [];
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }

    /**
     * Ensure that session have proper section.
     */
    private function resumeSection(): void
    {
        $this->session->resume();

        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
    }
}
