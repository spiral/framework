<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;

interface RouterInterface extends RequestHandlerInterface
{
    /**
     * Set route.
     */
    public function setRoute(string $name, RouteInterface $route): void;

    /**
     * Default route is needed as fallback if no other route matched the request.
     */
    public function setDefault(RouteInterface $route): void;

    /**
     * Get route by it's name.
     *
     * @throws UndefinedRouteException
     */
    public function getRoute(string $name): RouteInterface;

    /**
     * Get all registered routes.
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Generate valid route URL using route name and set of parameters. Should support controller
     * and action name separated by ":" - in this case router should find appropriate route and
     * create url using it.
     *
     * @param string             $route Route name.
     * @param array|\Traversable $parameters Routing parameters.
     *
     * @throws RouteException
     * @throws UndefinedRouteException
     */
    public function uri(string $route, iterable $parameters = []): UriInterface;

    public function import(RoutingConfigurator $routes): void;
}
