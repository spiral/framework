<?php

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Core\FactoryInterface;

/**
 * Manages the presets for various route groups.
 */
final class GroupRegistry implements \IteratorAggregate
{
    /** @var array<non-empty-string, RouteGroup> */
    private array $groups = [];

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    public function getGroup(string $name): RouteGroup
    {
        if (!isset($this->groups[$name])) {
            $this->groups[$name] = $this->factory->make(RouteGroup::class);
        }

        return $this->groups[$name];
    }

    /**
     * @return \ArrayIterator<array-key, RouteGroup>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->groups);
    }
}
