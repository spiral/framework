<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Core\Exception\ScopeException;

final class SectionScope implements SessionSectionInterface
{
    /** @var SessionInterface */
    private $session;

    /*** @var string */
    private $name;

    /**
     * @param SessionScope $session
     * @param string       $name
     */
    public function __construct(SessionScope $session, string $name)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * Shortcut for get.
     *
     * @param string $name
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
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     */
    public function __unset(string $name): void
    {
        $this->delete($name);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable
    {
        return $this->getActiveSection()->getIterator();
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->getActiveSection()->offsetExists($offset);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getActiveSection()->offsetGet($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->getActiveSection()->offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->getActiveSection()->offsetUnset($offset);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->getActiveSection()->getAll();
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, $value): void
    {
        $this->getActiveSection()->set($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return $this->getActiveSection()->has($name);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name, $default = null)
    {
        return $this->getActiveSection()->get($name, $default);
    }

    /**
     * @inheritDoc
     */
    public function pull(string $name, $default = null)
    {
        return $this->getActiveSection()->pull($name, $default);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $name): void
    {
        $this->getActiveSection()->delete($name);
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->getActiveSection()->clear();
    }

    /**
     * @return SessionSectionInterface
     *
     * @throws ScopeException
     */
    private function getActiveSection(): SessionSectionInterface
    {
        return $this->session->getActiveSession()->getSection($this->name);
    }
}
