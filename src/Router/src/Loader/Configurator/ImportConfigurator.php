<?php

declare(strict_types=1);

namespace Spiral\Router\Loader\Configurator;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\CoreInterface;
use Spiral\Router\RouteCollection;

final class ImportConfigurator
{
    public function __construct(
        private readonly RouteCollection $parent,
        private readonly RouteCollection $routes
    ) {
    }

    public function __sleep(): array
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }

    public function defaults(array $defaults): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->defaults($defaults);
        }

        return $this;
    }

    public function group(string $group): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->group($group);
        }

        return $this;
    }

    public function prefix(string $prefix): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->prefix($prefix);
        }

        return $this;
    }

    public function core(CoreInterface $core): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->core($core);
        }

        return $this;
    }

    public function middleware(MiddlewareInterface|string|array $middleware): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->middleware($middleware);
        }

        return $this;
    }

    public function __destruct()
    {
        $this->parent->addCollection($this->routes);
    }
}
