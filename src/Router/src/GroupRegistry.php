<?php

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Core\FactoryInterface;

/**
 * Manages the presets for various route groups.
 *
 * @implements \IteratorAggregate<non-empty-string, RouteGroup>
 */
final class GroupRegistry implements \IteratorAggregate
{
    /** @var non-empty-string */
    private string $defaultGroup = 'web';

    /** @var array<non-empty-string, RouteGroup> */
    private array $groups = [];

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    /**
     * @param non-empty-string $name
     */
    public function getGroup(string $name): RouteGroup
    {
        if (!isset($this->groups[$name])) {
            $this->groups[$name] = $this->factory->make(RouteGroup::class);
        }

        return $this->groups[$name];
    }

    /**
     * @param non-empty-string $group
     */
    public function setDefaultGroup(string $group): self
    {
        $this->defaultGroup = $group;

        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function getDefaultGroup(): string
    {
        return $this->defaultGroup;
    }

    /**
     * Push routes from each group to the router.
     *
     * @internal
     */
    public function registerRoutes(RouterInterface $router): void
    {
        foreach ($this->groups as $group) {
            $group->register($router, $this->factory);
        }
    }

    /**
     * @return \ArrayIterator<non-empty-string, RouteGroup>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->groups);
    }
}
