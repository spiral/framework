<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;

class Route extends AbstractRoute
{
    /**
     * Use this string as your target action to resolve action from routed URL.
     *
     * Example:
     * new Route('name', 'userPanel/<action>', 'Controllers\UserPanel::<action>');
     *
     * Attention, you can't route controllers this way, use DirectRoute for such purposes.
     */
    const DYNAMIC_ACTION = '<action>';

    /**
     * Declared route target, can be middleware (instance or class name), controller/action combination
     * specified using full class name and :: separator or closure.
     *
     * @var null
     */
    protected $target = null;

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
     * @param string          $name    Route name.
     * @param string          $pattern Route pattern.
     * @param string|callable $target  Route target.
     * @param array           $defaults
     */
    public function __construct($name, $pattern, $target, array $defaults = array())
    {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->target = $target;
        $this->defaults = $defaults;
    }

    /**
     * Perform route on given Request and return response.
     *
     * @param ServerRequestInterface $request
     * @param Container              $container          Container is required to get valid middleware
     *                                                   instance.
     * @param array                  $middlewaresAliases Middleware aliases provided from parent router.
     * @return mixed
     */
    public function perform(
        ServerRequestInterface $request,
        Container $container,
        array $middlewaresAliases = array()
    )
    {
        if (empty($this->middlewares))
        {
            if ($this->target instanceof \Closure)
            {
                $reflection = new \ReflectionFunction($this->target);

                return $reflection->invokeArgs(
                    $container->resolveArguments($reflection,
                        array(
                            'request' => $request,
                            'route'   => $this
                        )
                    ));
            }

            return call_user_func($this->getCallable($container), $request);
        }

        return $this->getPipeline($container, $middlewaresAliases)
            ->target($this->getCallable($container))
            ->run($request);
    }

    /**
     * Get callable route target.
     *
     * @param Container $container
     * @return callable
     */
    protected function getCallable(Container $container)
    {
        if (is_object($this->target) || is_array($this->target))
        {
            return $this->target;
        }

        if (is_string($this->target) && strpos($this->target, self::CONTROLLER_SEPARATOR) === false)
        {
            //Middleware
            return $container->get($this->target);
        }

        return function (ServerRequestInterface $request) use ($container)
        {
            //Calling controller (using core resolved via container)
            return $this->callAction($container->get('Spiral\Core\CoreInterface'), $request);
        };
    }

    /**
     * Call controller/action resolved from route target.
     *
     * @param CoreInterface          $core
     * @param ServerRequestInterface $serverRequestInterface
     * @return string
     */
    protected function callAction(CoreInterface $core, ServerRequestInterface $serverRequestInterface)
    {
        list($controller, $action) = explode(self::CONTROLLER_SEPARATOR, $this->target);

        if ($action == self::DYNAMIC_ACTION)
        {
            $action = $this->matches['action'];
        }

        return $core->callAction($controller, $action, $this->matches);
    }
}