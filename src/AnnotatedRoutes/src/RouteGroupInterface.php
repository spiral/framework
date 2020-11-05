<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterface;

interface RouteGroupInterface
{
    /**
     * Prefix added to all the routes.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): RouteGroupInterface;

    /**
     * @param CoreInterface|string|Autowire $core
     * @return $this
     */
    public function setCore($core): RouteGroupInterface;

    /**
     * @param MiddlewareInterface|string $middleware
     * @return $this
     */
    public function addMiddleware($middleware): RouteGroupInterface;

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
    ): void;

    /**
     * Push routes to router.
     */
    public function flushRoutes(): void;

    /**
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return Route
     */
    public function createRoute(string $pattern, string $controller, string $action): RouteInterface;
}
