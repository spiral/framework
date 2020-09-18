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
    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $prefix = '';

    /** @var Pipeline */
    private $pipeline;

    /** @var Router */
    private $router;

    /** @var array */
    private $routes = [];

    /** @var CoreInterface */
    private $core;

    /**
     * @param ContainerInterface $container
     * @param RouterInterface    $router
     * @param Pipeline           $pipeline
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        Pipeline $pipeline
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->pipeline = $pipeline;
    }

    /**
     * Prefix added to all the routes.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        // update routes
        $this->flushRoutes();

        return $this;
    }

    /**
     * @param CoreInterface|string|Autowire $core
     * @return $this
     */
    public function setCore($core): self
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
     * @param MiddlewareInterface|string $middleware
     * @return $this
     */
    public function addMiddleware($middleware): self
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
     *
     * @param string $name
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @param array  $verbs
     * @param array  $defaults
     * @param array  $middleware
     */
    public function registerRoute(
        string $name,
        string $pattern,
        string $controller,
        string $action,
        array $verbs,
        array $defaults,
        array $middleware
    ) {
        $this->routes[$name] = [
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $action,
            'verbs'      => $verbs,
            'defaults'   => $defaults,
            'middleware' => $middleware
        ];
    }

    /**
     * Push routes to router.
     */
    public function flushRoutes()
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

    /**
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return Route
     */
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
