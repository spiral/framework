<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Exception\Scope\NamedScopeDuplicationException;

/**
 * @internal
 */
final class Scope
{
    private ?\Spiral\Core\Container $parent = null;
    private ?self $parentScope = null;

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
    public function setParent(\Spiral\Core\Container $parent, self $parentScope): void
    {
        $this->parent = $parent;
        $this->parentScope = $parentScope;

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
