<?php

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Core\Container\InjectableInterface;

/**
 * Represents part of _SESSION array.
 */
final class SessionSection implements SessionSectionInterface, InjectableInterface
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $name
    ) {
    }

    /**
     * Shortcut for get.
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function __unset(string $name): void
    {
        $this->delete($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getAll());
    }

    public function getAll(): array
    {
        $this->resumeSection();

        return $_SESSION[$this->name];
    }

    public function set(string $name, mixed $value): self
    {
        $this->resumeSection();

        $_SESSION[$this->name][$name] = $value;

        return $this;
    }

    public function has(string $name): bool
    {
        $this->resumeSection();

        return \array_key_exists($name, $_SESSION[$this->name]);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (!$this->has($name)) {
            return $default;
        }

        return $_SESSION[$this->name][$name];
    }

    public function pull(string $name, mixed $default = null): mixed
    {
        $value = $this->get($name, $default);
        $this->delete($name);

        return $value;
    }

    public function delete(string $name): void
    {
        $this->resumeSection();
        unset($_SESSION[$this->name][$name]);
    }

    public function clear(): void
    {
        $this->resumeSection();
        $_SESSION[$this->name] = [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
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
