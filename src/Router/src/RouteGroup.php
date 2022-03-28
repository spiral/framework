<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Target\Action;

/**
 * RouteGroup provides the ability to configure multiple routes to controller/actions using same presets.
 */
final class RouteGroup
{
    private string $prefix = '';
    private array $routes = [];
    private ?CoreInterface $core = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RouterInterface $router,
        private readonly Pipeline $pipeline
    ) {
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

    public function addMiddleware(MiddlewareInterface|string $middleware): self
    {
        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $this->container->get($middleware);
        }

        $this->pipeline->pushMiddleware($middleware);

        // update routes
        $this->flushRoutes();

        return $this;
    }

    /**
     * Register route to group.
     */
    public function registerRoute(
        string $name,
        string $pattern,
        string $controller,
        string $action,
        array $verbs,
        array $defaults,
        array $middleware
    ): void {
        $this->routes[$name] = [
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $action,
            'verbs'      => $verbs,
            'defaults'   => $defaults,
            'middleware' => $middleware,
        ];
    }

    /**
     * Push routes to router.
     */
    public function flushRoutes(): void
    {
        foreach ($this->routes as $name => $schema) {
            $route = $this->createRoute($schema['pattern'], $schema['controller'], $schema['action']);

            if ($schema['defaults'] !== []) {
                $route = $route->withDefaults($schema['defaults']);
            }

            $this->router->setRoute(
                $name,
                $route->withVerbs(...$schema['verbs'])->withMiddleware(...$schema['middleware'])
            );
        }
    }

    public function createRoute(string $pattern, string $controller, string $action): Route
    {
        $action = new Action($controller, $action);
        if ($this->core !== null) {
            $action = $action->withCore($this->core);
        }

        $route = new Route($this->prefix . $pattern, $action);

        // all routes within group share the same middleware pipeline
        $route = $route->withMiddleware($this->pipeline);

        return $route;
    }
}
