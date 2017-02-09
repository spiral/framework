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
use Spiral\Core\ContainerInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\RouterException;
use Spiral\Http\Exceptions\UndefinedRouteException;

/**
 * Spiral implementation of RouterInterface.
 */
class Router implements RouterInterface
{
    /**
     * Every route should be executed in a context of base path.
     *
     * @var string
     */
    private $basePath = '/';

    /**
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * Primary route (fallback if no routes work).
     *
     * @var RouteInterface
     */
    private $defaultRoute = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritdoc}
     *
     * @param RouteInterface|array $default  Default route or options to construct instance of
     *                                       DirectRoute.
     * @param string               $basePath Automatically added to all urls.
     *
     * @throws RouterException
     */
    public function __construct(ContainerInterface $container, string $basePath = '/')
    {
        $this->basePath = $basePath;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        //Defining our scope
        $scope = $this->container->replace(RouterInterface::class, $this);
        try {
            //Trying to find matched route
            $route = $this->findRoute($request);

            if (empty($route)) {
                //Unable to locate route
                throw new ClientException(ClientException::NOT_FOUND);
            }

            //Route must be executed in a specific container scope
            $route = $route->withContainer($this->container);

            //Executing with route attribute (can be resolved via RouteInterface)
            return $route->__invoke(
                $request->withAttribute('route', $route),
                $response
            );
        } finally {
            $this->container->restore($scope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route)
    {
        //Each added route must inherit basePath prefix
        $this->routes[] = $route->withPrefix($this->basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultRoute(RouteInterface $route)
    {
        $this->defaultRoute = $route->withPrefix($this->basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute(string $name): RouteInterface
    {
        if (!empty($this->defaultRoute) && $this->defaultRoute->getName() == $name) {
            return $this->defaultRoute;
        }

        foreach ($this->routes as $route) {
            if ($route->getName() == $name) {
                return $route;
            }
        }

        throw new UndefinedRouteException("Undefined route '{$name}'");
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UndefinedRouteException
     */
    public function uri(string $route, $parameters = []): UriInterface
    {
        try {
            return $this->getRoute($route)->uri($parameters);
        } catch (UndefinedRouteException $e) {
            //In some cases route name can be provided as controller:action pair, we can try to
            //generate such route automatically based on our default/fallback route
            return $this->castRoute($route)->uri($parameters);
        }
    }

    /**
     * Find route matched for given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return null|RouteInterface
     */
    protected function findRoute(ServerRequestInterface $request)
    {
        foreach ($this->routes as $route) {
            //Route might return altered version on matching (route with populated parameters)
            $matched = $route->match($request);

            if (!empty($matched)) {
                if ($matched instanceof RouteInterface) {
                    return $matched;
                }

                throw new RouterException("Matched route must return RouteInterface instance");
            }
        }

        if (!empty($this->defaultRoute) && !empty($matched = $this->defaultRoute->match($request))) {
            //Trying to use default route as fallback
            return $matched;
        }

        return null;
    }

    /**
     * Helper function used to reconfigure default route (usually controller route) with set of
     * parameters related to selected controller and action.
     *
     * @param string $route
     *
     * @return RouteInterface
     * @throws UndefinedRouteException
     */
    private function castRoute(string $route): RouteInterface
    {
        //Will be handled via default route where route name is specified as controller::action
        if (strpos($route, ':') === false) {
            throw new UndefinedRouteException(
                "Unable to locate route or use default route with 'controller:action' pattern"
            );
        }

        if (empty($this->defaultRoute)) {
            throw new UndefinedRouteException("Default/fallback route is missing");
        }

        //We can fetch controller and action names from url
        list($controller, $action) = explode(':', str_replace(['/', '::'], ':', $route));

        //Let's create new route for a controller and action
        $route = $this->defaultRoute->withName($route)->withDefaults([
            'controller' => $controller,
            'action'     => $action
        ]);

        //For future requests
        $this->addRoute($route);

        return $route;
    }
}
