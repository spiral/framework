<?php

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Router\Loader\Configurator\RouteConfigurator;

/**
 * @implements \IteratorAggregate<non-empty-string, RouteConfigurator>
 */
class RouteCollection implements \IteratorAggregate, \Countable
{
    /** @var array<non-empty-string, RouteConfigurator> */
    private array $routes = [];

    public function __clone()
    {
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * @return \ArrayIterator<non-empty-string, RouteConfigurator>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Gets the number of Routes in this collection.
     */
    public function count(): int
    {
        return \count($this->routes);
    }

    /**
     * @param non-empty-string $name
     *
     * @return void
     *
     * TODO add return type
     */
    public function add(string $name, RouteConfigurator $route)
    {
        $this->routes[$name] = $route;
    }

    /**
     * Returns all routes in this collection.
     *
     * @return array<non-empty-string, RouteConfigurator>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Gets a route by name.
     *
     * @param non-empty-string $name
     */
    public function get(string $name): ?RouteConfigurator
    {
        return $this->routes[$name] ?? null;
    }

    /**
     * Check a route by name.
     *
     * @param non-empty-string $name
     */
    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * Removes a route or an array of routes by name from the collection.
     *
     * @param non-empty-string|non-empty-string[] $name The route name or an array of route names
     *
     * @return void
     *
     * TODO add return type
     */
    public function remove(string|array $name)
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }

    /**
     * @return void
     *
     * TODO add return type
     */
    public function addCollection(self $collection)
    {
        foreach ($collection->all() as $name => $route) {
            $this->routes[$name] = $route;
        }
    }

    /**
     * Adds a specific group to a route.
     *
     * @param non-empty-string $group
     *
     * @return void
     *
     * TODO add return type
     */
    public function group(string $group)
    {
        foreach ($this->routes as $route) {
            $route->group($group);
        }
    }
}
