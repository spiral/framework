<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\RouterException;
use Spiral\Http\Exceptions\UndefinedRouteException;

/**
 * Spiral implementation of RouterInterface.
 *
 * @todo potentially add ability to work as middleware with $next call
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
     * @param RouteInterface|array $default Default route or options to construct instance of
     *                                      DirectRoute.
     * @param bool                 $keepOutput
     * @throws RouterException
     */
    public function __construct(ContainerInterface $container, $basePath = '/')
    {
        $this->basePath = $basePath;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $scope = $this->container->replace(RouterInterface::class, $this);

        try {
            $route = $this->findRoute($request);

            if (empty($route)) {
                //Unable to locate route
                throw new ClientException(ClientException::NOT_FOUND);
            }

            //IoC container context
            $response = $route->withContainer($this->container)->perform(
                $request->withAttribute('route', $route),
                $response
            );
        } finally {
            $this->container->restore($scope);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route)
    {
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
    public function getRoute($name)
    {
        foreach ($this->routes as $route) {
            if ($route->getName() == $name) {
                return $route;
            }
        }

        throw new UndefinedRouteException("Undefined route '{$name}'.");
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UndefinedRouteException
     */
    public function uri($route, $parameters = [])
    {
        try {
            return $this->getRoute($route)->uri($parameters, $this->basePath);
        } catch (UndefinedRouteException $e) {
            //Cast and retry
            return $this->castRoute($route)->uri($parameters);
        }
    }

    /**
     * Find route matched for given request.
     *
     * @param ServerRequestInterface $request
     * @return null|RouteInterface
     */
    protected function findRoute(ServerRequestInterface $request)
    {
        foreach ($this->routes as $route) {

            if (!empty($matched = $route->match($request))) {
                if ($matched instanceof RouteInterface) {
                    return $matched;
                }

                throw new RouterException("Matched route must return RouteInterface instance");
            }
        }

        if (
            !empty($this->defaultRoute)
            && !empty($matched = $this->defaultRoute->match($request))
        ) {
            return $matched;
        }

        return null;
    }

    /**
     * @param string $route
     * @return RouteInterface
     * @throws UndefinedRouteException
     */
    private function castRoute($route)
    {
        //Will be handled via default route where route name is specified as controller::action
        if (strpos($route, ':') === false) {
            throw new UndefinedRouteException(
                "Unable to locate route or use default route with 'controller:action' pattern."
            );
        }

        if (empty($this->defaultRoute)) {
            throw new UndefinedRouteException("Default/fallback route is missing.");
        }

        //We can fetch controller and action names from url
        list($controller, $action) = explode(':', str_replace(['/', '::'], ':', $route));

        $route = $this->defaultRoute->withName($route)->withDefaults([
            'controller' => $controller,
            'action'     => $action
        ]);

        //For future requests
        $this->addRoute($route);

        return $route;
    }
}
