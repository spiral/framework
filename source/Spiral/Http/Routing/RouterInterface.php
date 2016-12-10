<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\RouteException;
use Spiral\Http\Exceptions\RouterException;
use Spiral\Http\Exceptions\UndefinedRouteException;

/**
 * Routers used by HttpDispatcher and other components for logical routing to controller actions.
 */
interface RouterInterface
{
    /**
     * Valid endpoint for MiddlewarePipeline.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     * @throws ClientException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;

    /**
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route);

    /**
     * Default route is needed as fallback if no other route matched the request.
     *
     * @param RouteInterface $route
     */
    public function defaultRoute(RouteInterface $route);

    /**
     * Get route by it's name.
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws RouterException
     * @throws UndefinedRouteException
     */
    public function getRoute($name): RouteInterface;

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
     * @param array|\Traversable $parameters
     *
     * @return UriInterface
     * @throws RouterException
     * @throws RouteException
     * @throws UndefinedRouteException
     */
    public function uri(string $route, $parameters = []): UriInterface;
}