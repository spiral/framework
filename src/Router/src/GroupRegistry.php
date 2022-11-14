<?php

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Core\FactoryInterface;

/**
 * Manages the presets for various route groups.
 */
final class GroupRegistry implements \IteratorAggregate
{
    private string $defaultGroup = 'web';

    /** @var array<non-empty-string, RouteGroup> */
    private array $groups = [];

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    public function getGroup(string $name): RouteGroup
    {
        if (!isset($this->groups[$name])) {
            $this->groups[$name] = $this->factory->make(RouteGroup::class, ['name' => $name]);
        }

        return $this->groups[$name];
    }

    public function setDefaultGroup(string $group): self
    {
        $this->defaultGroup = $group;

        return $this;
    }

    public function getDefaultGroup(): string
    {
        return $this->defaultGroup;
    }

    /**
     * @return \ArrayIterator<array-key, RouteGroup>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->groups);
    }
}
