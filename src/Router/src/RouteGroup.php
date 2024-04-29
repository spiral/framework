<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Router\Target\AbstractTarget;

/**
 * RouteGroup provides the ability to configure multiple routes to controller/actions using same presets.
 *
 * @psalm-type MiddlewareType = MiddlewareInterface|class-string<MiddlewareInterface>|non-empty-string|Autowire
 */
final class RouteGroup
{
    private string $prefix = '';
    private string $namePrefix = '';

    /** @var array<non-empty-string, Route> */
    private array $routes = [];

    /** @var array<MiddlewareType> */
    private array $middleware = [];

    private Autowire|HandlerInterface|CoreInterface|string|null $core = null;

    public function __construct(
        /** @deprecated since v3.3.0 */
        private readonly ?ContainerInterface $container = null,
        /** @deprecated since v3.3.0 */
        private readonly ?RouterInterface $router = null,
        /** @deprecated since v3.3.0 */
        private readonly ?UriHandler $handler = null
    ) {
    }

    /**
     * Check if group has a route with given name
     */
    public function hasRoute(string $name): bool
    {
        return \array_key_exists($name, $this->routes);
    }

    /**
     * Prefix added to all the routes.
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Route name prefix added to all routes.
     */
    public function setNamePrefix(string $prefix): self
    {
        $this->namePrefix = $prefix;

        return $this;
    }

    public function setCore(Autowire|CoreInterface|HandlerInterface|string $core): self
    {
        $this->core = $core;

        return $this;
    }

    /**
     * @param MiddlewareType $middleware
     */
    public function addMiddleware(MiddlewareInterface|Autowire|string $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Push routes to router.
     *
     * @internal
     */
    public function register(RouterInterface $router, FactoryInterface $factory): void
    {
        foreach ($this->routes as $name => $route) {
            if ($this->core !== null) {
                if (!$this->core instanceof CoreInterface && !$this->core instanceof HandlerInterface) {
                    $this->core = $factory->make($this->core);
                }

                $target = $route->getTarget();
                if ($target instanceof AbstractTarget) {
                    $route = $route->withTarget($target->withCore($this->core));
                }
            }

            try {
                $uriHandler = $route->getUriHandler();
            } catch (\Throwable) {
                $uriHandler = $factory->make(UriHandler::class);
            }

            $router->setRoute(
                $name,
                $route
                    ->withUriHandler($uriHandler->withPrefix($this->prefix))
                    ->withMiddleware(...$this->middleware)
            );
        }
    }

    /**
     * Add a route to a route group.
     *
     * @param non-empty-string $name
     *
     * @psalm-assert Route $route
     */
    public function addRoute(string $name, RouteInterface $route): self
    {
        \assert($route instanceof Route);

        $this->routes[$this->namePrefix . $name] = $route;

        return $this;
    }
}
