<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Exception\Scope\NamedScopeDuplicationException;
use Spiral\Core\FactoryInterface;

/**
 * @internal
 */
final class Scope
{
    private ?\Spiral\Core\Container $parent = null;
    private ?self $parentScope = null;
    private ?FactoryInterface $parentFactory = null;

    public function __construct(
        private readonly ?string $scopeName = null,
    ) {
    }

    public function getScopeName(): ?string
    {
        return $this->scopeName;
    }

    /**
     * Link the current scope with its parent scope and container.
     *
     * @throws NamedScopeDuplicationException
     */
    public function setParent(\Spiral\Core\Container $parent, self $parentScope, FactoryInterface $factory): void
    {
        $this->parent = $parent;
        $this->parentScope = $parentScope;
        $this->parentFactory = $factory;

        // Check a scope with the same name is not already registered
        if ($this->scopeName !== null) {
            $tmp = $this;
            while ($tmp->parentScope !== null) {
                $tmp = $tmp->parentScope;
                $tmp->scopeName !== $this->scopeName ?: throw new NamedScopeDuplicationException($this->scopeName);
            }
        }
    }

    public function getParent(): ?\Spiral\Core\Container
    {
        return $this->parent;
    }

    public function getParentFactory(): ?FactoryInterface
    {
        return $this->parentFactory;
    }

    /**
     * Return list of parent scope names.
     * The first element is the current scope name, and the next is the closest parent scope name...
     *
     * @return array<int<0, max>, string|null>
     */
    public function getParentScopeNames(): array
    {
        $result = [$this->scopeName];

        $parent = $this;
        while ($parent->parentScope !== null) {
            $parent = $parent->parentScope;
            $result[] = $parent->scopeName;
        }

        return $result;
    }

    public function getParentScope(): ?self
    {
        return $this->parentScope;
    }

    public function destruct(): void
    {
        $this->parent = null;
        $this->parentScope = null;
    }
}
