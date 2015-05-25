<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Spiral\Core\Container;

trait RouterTrait
{
    /**
     * Container.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Set of pre-defined routes to be send to Router and passed thought request later.
     *
     * @var RouteInterface[]
     */
    protected $routes = array();

    /**
     * Router middleware used by HttpDispatcher and modules to perform URI based routing with defined
     * endpoint such as controller action, closure or middleware.
     *
     * @var Router
     */
    protected $router = null;

    /**
     * Get Router instance.
     *
     * @return Router
     */
    public function getRouter()
    {
        if (!empty($this->router))
        {
            return $this->router;
        }

        return $this->router = $this->createRouter();
    }

    /**
     * Create router instance with aggregated routes.
     *
     * @return Router
     */
    protected function createRouter()
    {
        return Router::make(array(
            'container' => $this->container,
            'routes'    => $this->routes
        ), $this->container);
    }

    /**
     * Register new RouteInterface instance.
     *
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route)
    {
        $this->routes[] = $route;
        if (!empty($this->router))
        {
            $this->router->addRoute($route);
        }
    }

    /**
     * Register new route using defined pattern and target.
     *
     * Route examples:
     *
     * Default routing (controller/action/id);
     * Http::route('default', '<controller>Controller(/<action>(/<id>))', array(
     *      'controller' => 'home'
     * ));
     *
     * You can create static routes (route part without segments).
     * Http::route('profile-<id>', 'UserController::showProfile');
     * Http::route('profile-<id>', 'UserController::showProfile');
     *
     * Optional segments:
     * Http::route('profile(/<id>)', 'UserController::showProfile');
     *
     * This route will react on URL's like /profile/ and /profile/someText/
     *
     * To determinate your own pattern for segment use construction <segmentName:pattern>
     * Http::route('profile(/<id:\d+>)', 'UserController::showProfile');
     *
     * Will react only on /profile/ and /profile/1384978/
     *
     * You can use custom pattern for controller and action segments.
     * Http::route('<controller:users>(/<action:edit|save|open>)', '<controller>Controller::<action>');
     *
     * Including http:// or https:// to route patter will force routing including domain name
     * (baseURL will be ignored in this case).
     * Http::route(
     *      'http://<username>\.website\.com(/<controller>(/<action>(/<id>)))',
     *      'Controllers\Inner\<controller>Controller::<action>'
     * );
     *
     * Routes can be used non only with string target:
     * Http::route('users', function ()
     * {
     *      return "This is users route.";
     * });
     *
     * Or be associated with middleware:
     * Http::route('/something(/<value>)', new MyMiddleware());
     *
     * @param string $pattern  Route pattern.
     * @param mixed  $target   Route target, can be either controller expression (including action
     *                         name separated by ::), closure or middleware.
     * @param array  $defaults Set of default patter values.
     * @return Route
     */
    public function route($pattern, $target = null, array $defaults = array())
    {
        $this->addRoute($route = new Route($this->container, $pattern, $pattern, $target, $defaults));

        return $route;
    }

    /**
     * Create route to map to controller methods based on HTTP method, default url patterns is:
     *
     * GET     /resource      => Controller->index()
     * PUT     /resource      => Controller->create()
     * POST    /resource      => Controller->create()
     * GET     /resource/id   => Controller->retrieve(id)
     * PUT     /resource/id   => Controller->update(id)
     * POST    /resource/id   => Controller->update(id)
     * DELETE  /resource/id   => Controller->delete(id)
     *
     * @param string $resource   Resource name.
     * @param string $controller Controller class.
     * @return ResourceRoute
     */
    public function resource($resource, $controller)
    {
        $this->addRoute($route = new ResourceRoute($this->container, $resource, $controller));

        return $route;
    }
}