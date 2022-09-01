<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Target\AbstractTarget;

/**
 * RouteGroup provides the ability to configure multiple routes to controller/actions using same presets.
 */
final class RouteGroup
{
    private string $prefix = '';

    /** @var string[] */
    private array $routes = [];

    /** @var array<class-string<MiddlewareInterface>|MiddlewareInterface|Autowire> */
    private array $middleware = [];

    private ?CoreInterface $core = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RouterInterface $router,
        private readonly UriHandler $handler
    ) {
    }

    /**
     * Check if group has a route with given name
     */
    public function hasRoute(string $name): bool
    {
        return \in_array($name, $this->routes);
    }

    /**
     * Prefix added to all the routes.
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        // update routes
        $this->flushRoutes();

        return $this;
    }

    public function setCore(Autowire|CoreInterface|string $core): self
    {
        if (!$core instanceof CoreInterface) {
            $core = $this->container->get($core);
        }
        $this->core = $core;

        // update routes
        $this->flushRoutes();

        return $this;
    }

    /**
     * @param MiddlewareInterface|Autowire|class-string<MiddlewareInterface>|non-empty-string $middleware
     */
    public function addMiddleware(MiddlewareInterface|Autowire|string $middleware): self
    {
        $this->middleware[] = $middleware;

        // update routes
        $this->flushRoutes();

        return $this;
    }

    /**
     * Push routes to router.
     *
     * @internal
     */
    public function flushRoutes(): void
    {
        foreach ($this->routes as $name) {
            $this->router->setRoute($name, $this->applyGroupParams($this->router->getRoute($name)));
        }
    }

    /**
     * Add a route to a route group.
     */
    public function addRoute(string $name, Route $route): self
    {
        $this->routes[] = $name;

        $this->router->setRoute($name, $this->applyGroupParams($route));

        return $this;
    }

    private function applyGroupParams(Route $route): Route
    {
        if ($this->core !== null) {
            $target = $route->getTarget();

            if ($target instanceof AbstractTarget) {
                $route = $route->withTarget($target->withCore($this->core));
            }
        }

        return $route
            ->withUriHandler($this->handler->withPrefix($this->prefix))
            ->withMiddleware(...$this->middleware);
    }
}
