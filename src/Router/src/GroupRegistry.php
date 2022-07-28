<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Spiral\Core\FactoryInterface;

/**
 * Manages the presets for various route groups.
 */
final class GroupRegistry implements \IteratorAggregate
{
    private ContainerInterface $factory;

    /** @var RouteGroup[] */
    private array $groups = [];

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
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
