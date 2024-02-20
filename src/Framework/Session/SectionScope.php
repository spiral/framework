<?php

declare(strict_types=1);

namespace Spiral\Session;

/**
 * @deprecated Use {@see SessionInterface::getSection()} instead. Will be removed in v4.0.
 */
final class SectionScope implements SessionSectionInterface
{
    public function __construct(
        private readonly SessionScope $session,
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

    public function getIterator(): \Traversable
    {
        return $this->getActiveSection()->getIterator();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->getActiveSection()->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getActiveSection()->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->getActiveSection()->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->getActiveSection()->offsetUnset($offset);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAll(): array
    {
        return $this->getActiveSection()->getAll();
    }

    public function set(string $name, mixed $value): SessionSectionInterface
    {
        return $this->getActiveSection()->set($name, $value);
    }

    public function has(string $name): bool
    {
        return $this->getActiveSection()->has($name);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->getActiveSection()->get($name, $default);
    }

    public function pull(string $name, mixed $default = null): mixed
    {
        return $this->getActiveSection()->pull($name, $default);
    }

    public function delete(string $name): void
    {
        $this->getActiveSection()->delete($name);
    }

    public function clear(): void
    {
        $this->getActiveSection()->clear();
    }

    private function getActiveSection(): SessionSectionInterface
    {
        return $this->session->getActiveSession()->getSection($this->name);
    }
}
