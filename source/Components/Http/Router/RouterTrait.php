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
     * Set of pre-defined routes to be send to Router and passed thought request later.
     *
     * @var RouteInterface[]
     */
    protected $routes = [];

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
        return Router::make(['routes' => $this->routes]);
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
     * New instance of spiral Route. Route can support callable targets, controller/action association
     * including actions resolved from route itself and etc.
     *
     * Example (given in a context of application bootstrap method):
     *
     * Static routes.
     *      $this->http->route('profile-<id>', 'Controllers\UserController::showProfile');
     *      $this->http->route('profile-<id>', 'Controllers\UserController::showProfile');
     *
     * Dynamic actions:
     *      $this->http->route('account/<action>', 'Controllers\AccountController::<action>');
     *
     * Optional segments:
     *      $this->http->route('profile(/<id>)', 'Controllers\UserController::showProfile');
     *
     * This route will react on URL's like /profile/ and /profile/someSegment/
     *
     * To determinate your own pattern for segment use construction <segmentName:pattern>
     *      $this->http->route('profile(/<id:\d+>)', 'Controllers\UserController::showProfile');
     *
     * Will react only on /profile/ and /profile/1384978/
     *
     * You can use custom pattern for controller and action segments.
     * $this->http->route('users(/<action:edit|save|open>)', 'Controllers\UserController::<action>');
     *
     * Routes can be applied to URI host.
     * $this->http->route(
     *      '<username>.domain.com(/<action>(/<id>))',
     *      'Controllers\UserController::<action>'
     * )->useHost();
     *
     * Routes can be used non only with controllers (no idea why you may need it):
     * $this->http->route('users', function ()
     * {
     *      return "This is users route.";
     * });
     *
     * Or be associated with middleware:
     * $this->http->route('/something(/<value>)', new MyMiddleware());
     *
     * @param string          $pattern Route pattern.
     * @param string|callable $target  Route target.
     * @param array           $defaults
     * @return Route
     */
    public function route($pattern, $target = null, array $defaults = [])
    {
        if (is_string($target))
        {
            $name = $target;
        }
        else
        {
            $name = uniqid();
        }

        $this->addRoute($route = new Route($name, $pattern, $target, $defaults));

        return $route;
    }
}