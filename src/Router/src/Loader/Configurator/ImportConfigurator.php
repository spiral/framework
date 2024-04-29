<?php

declare(strict_types=1);

namespace Spiral\Router\Loader\Configurator;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Router\RouteCollection;

final class ImportConfigurator
{
    public function __construct(
        private readonly RouteCollection $parent,
        private readonly RouteCollection $routes
    ) {
    }

    public function __destruct()
    {
        $this->parent->addCollection($this->routes);
    }

    public function __sleep(): array
    {
        throw new \BadMethodCallException('Cannot unserialize ' . self::class);
    }

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . self::class);
    }

    public function defaults(array $defaults): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->defaults($defaults);
        }

        return $this;
    }

    /**
     * @param non-empty-string $group
     */
    public function group(string $group): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->group($group);
        }

        return $this;
    }

    /**
     * @param non-empty-string $prefix
     */
    public function prefix(string $prefix): self
    {
        foreach ($this->routes->all() as $configurator) {
            $configurator->prefix($prefix);
        }

        return $this;
    }

    /**
     * @param non-empty-string $prefix
     */
    public function namePrefix(string $prefix): self
    {
        foreach ($this->routes->all() as $name => $configurator) {
            $this->routes->add($prefix . $name, $configurator);
            $this->routes->remove($name);
        }

        return $this;
    }

    public function core(HandlerInterface|CoreInterface $core): self
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
}
