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
use Spiral\Components\Http\MiddlewareInterface;
use Spiral\Components\Http\MiddlewarePipe;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;

class Route extends Component implements RouteInterface
{
    /**
     * Default segment pattern, this patter can be applied to controller names, actions and etc.
     */
    const DEFAULT_SEGMENT = '[^\/]+';

    /**
     * Default separator to split controller and action name in route target.
     */
    const CONTROLLER_SEPARATOR = '::';

    /**
     * Route name used to identify route instance in router stack. This is required to generate urls
     * using route pattern and set of provided parameters.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Route pattern includes simplified regular expressing later compiled to real regexp. Pattern
     * with be applied to URI path with excluded active path value (to make routes work when application
     * located in folder and etc).
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * Route target, can be either controller expression (including action name separated by ::),
     * closure or middleware.
     *
     * @var mixed
     */
    protected $target = '';

    /**
     * Default set of values to fill route matches and target pattern (if specified as pattern).
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Additional conditions Request should met.
     *
     * @var array
     */
    protected $conditions = array();

    /**
     * Compiled route options, pattern and etc. Internal data.
     *
     * @var array
     */
    protected $compiled = array();

    /**
     * Result of regular expression. Matched can be used to fill target controller pattern or send
     * to controller method as arguments.
     *
     * @var array
     */
    protected $matches = array();

    /**
     * Middlewares associated with route. This is only aliases, real class names will be provided
     * by router to perform method. To pass additional parameters to nested middleware use options
     * argument while registering middleware (with() method), this options will be available to
     * middleware via $context->getOptions() method.
     *
     * @var array
     */
    protected $middlewares = array();

    /**
     * Custom route options, usually used to pass additional information to nested middleware, this
     * data will be available to middleware via $context->getOptions() method.
     *
     * @var array
     */
    protected $options = array();

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
     * @param string $name       Route name for url generation.
     * @param string $pattern    Route pattern.
     * @param mixed  $target     Route target, can be either controller expression (including action
     *                           name separated by ::), closure or middleware.
     * @param array  $defaults   Set of default patter values.
     * @param array  $conditions Pre-defined set of route conditions.
     * @return Route
     */
    public function __construct(
        $name,
        $pattern,
        $target,
        array $defaults = array(),
        array $conditions = array()
    )
    {
        if (empty($target))
        {
            throw new \InvalidArgumentException("Route target should not be empty.");
        }

        $this->name = $name;
        $this->pattern = $pattern;
        $this->target = $target;
        $this->defaults = $defaults;
        $this->conditions = $conditions;
    }

    /**
     * Get route name. Name is requires to correctly identify route inside router stack (to generate
     * url for example).
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Declared route pattern.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Declared route target.
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Route conditions.
     *
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Route options used to provide additional information to inner middleware.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set route name. This action should be performed BEFORE parent router will be created, in other
     * scenario route will be available under old name.
     *
     * @param string $name
     * @return string
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set default values (will be merged with current default) to be used in generated target or
     * passed to associated middleware.
     *
     * @param array $default
     * @return static
     */
    public function defaults(array $default)
    {
        $this->defaults = $default + $this->defaults;

        return $this;
    }

    /**
     * Add route-precondition to be applied to provided request instance. Current method supports
     * host, method and scheme preconditions.
     *
     * Example:
     * $route->where('method', 'GET')->where('host', ['domain.com', 'www.domain.com']);
     *
     * @param string       $condition Condition type, host, scheme or method values.
     * @param string|array $options   Allowed value or values.
     * @return static
     */
    public function where($condition, $options = null)
    {
        $this->conditions[$condition] = $options;

        return $this;
    }

    /**
     * Associated inner middleware with route. Middleware will be executed "at top" of real route
     * target such as controller. Attention, response provided from inner not necessary will be
     * type of ResponseInterface as real response wrapping will happen on higher HttpDispatcher
     * level.
     *
     * You can provide additional options to inner middleware using second argument, data will be
     * available via $context->getOptions() method where context is route itself.
     *
     * @param string|MiddlewareInterface|\Closure $middleware Inner middleware class, instance or
     *                                                        closure.
     * @param array                               $options    Provided options will be available via
     *                                                        $context->getOptions() method.
     * @return static
     */
    public function with($middleware, array $options = array())
    {
        $this->middlewares[] = $middleware;
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * Helper method used to compile simplified route pattern to valid regular expression.
     */
    protected function compileRoute()
    {
        $pattern = trim($this->pattern, '/');

        //Domain name should be specified
        $fullUri = false;
        if (strpos($pattern, 'http://') === 0 || strpos($pattern, 'https://') === 0)
        {
            $fullUri = true;
        }

        //Provided options
        $options = array();

        $replaces = array('/' => '\\/', '(' => '(?:', ')' => ')?');
        if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches))
        {
            $variables = array_combine($matches[1], $matches[2]);
            foreach ($variables as $name => $segment)
            {
                $segment = $segment ?: self::DEFAULT_SEGMENT;
                $replaces["<$name>"] = "(?P<$name>$segment)";
                $options[] = $name;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $pattern);

        $pattern = '/^' . strtr($template, $replaces) . '$/u';
        $template = stripslashes(str_replace(array(')', '(', '?'), '', $template));

        $this->compiled = array(
            'pattern'  => $pattern,
            'fullUri'  => $fullUri,
            'template' => $template,
            'options'  => $options
        );
    }

    /**
     * Check if route matched with provided request. Will check url pattern and pre-conditions.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function match(ServerRequestInterface $request)
    {
        if (!$this->checkConditions($request))
        {
            return false;
        }

        if (empty($this->compiled))
        {
            $this->compileRoute();
        }

        if ($this->compiled['fullUri'])
        {
            $uri = (string)$request->getUri()->withQuery('')->withFragment('');
        }
        else
        {
            $uri = substr($request->getUri()->getPath(), strlen($request->getAttribute('activePath')));
        }

        if (preg_match($this->compiled['pattern'], rtrim($uri, '/'), $this->matches))
        {
            $this->matches += $this->defaults + array_fill_keys($this->compiled['options'], null);

            if (isset($this->matches['controller']))
            {
                //Controller can only include following characters (removing namespace and etc).
                $this->matches['controller'] = preg_replace(
                    '/^[^a-z0-9_]$/i',
                    '',
                    $this->matches['controller']
                );
            }

            return true;
        }

        return false;
    }

    /**
     * Helper method to check request pre-conditions.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function checkConditions(ServerRequestInterface $request)
    {
        foreach ($this->conditions as $condition => $options)
        {
            $options = is_array($options) ? $options : array($options);
            switch ($condition)
            {
                case 'scheme':
                    if (!in_array($request->getUri()->getScheme(), $options))
                    {
                        return false;
                    }
                    break;

                case 'host':
                    if (!in_array($request->getUri()->getHost(), $options))
                    {
                        return false;
                    }
                    break;

                case 'method':
                    if (!in_array($request->getMethod(), $options))
                    {
                        return false;
                    }
                    break;

                default:
                    throw new RouterException("Undefined request condition '{$condition}'.");
            }
        }

        /**
         * More conditions can be added in future.
         */

        return true;
    }

    /**
     * Matches are populated after route matched request. Matched will include variable URL parts
     * merged with default values.
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Perform route on given Request and return response.
     *
     * @param ServerRequestInterface $request
     * @param CoreInterface          $core
     * @param array                  $routeMiddlewares Middleware aliases provided from parent router.
     * @return mixed
     */
    public function perform(
        ServerRequestInterface $request,
        CoreInterface $core,
        array $routeMiddlewares = array()
    )
    {
        if (empty($this->middlewares))
        {
            if ($this->target instanceof \Closure)
            {
                $reflection = new \ReflectionFunction($this->target);

                return $reflection->invokeArgs(Container::resolveArguments($reflection, array(
                    'request' => $request,
                    'context' => $this,
                    'route'   => $this
                )));
            }

            $target = $this->getTargetEndpoint($core);

            return $target($request, null, $this);
        }

        return $this->getPipeline($routeMiddlewares)
            ->target($this->getTargetEndpoint($core))
            ->run($request, $this);
    }

    /**
     * Construct route middleware pipeline.
     *
     * @param array $routeMiddlewares Middleware aliases provided from parent router.
     * @return MiddlewarePipe
     */
    protected function getPipeline(array $routeMiddlewares = array())
    {
        $middlewares = array();
        foreach ($this->middlewares as $middleware)
        {
            //Resolving middleware aliases
            $middlewares[] = isset($routeMiddlewares[$middleware])
                ? $routeMiddlewares[$middleware]
                : $middleware;
        }

        return new MiddlewarePipe($middlewares);
    }

    /**
     * Build callable route target.
     *
     * @param CoreInterface $core
     * @return callable|mixed|null|object
     */
    protected function getTargetEndpoint(CoreInterface $core)
    {
        if (is_object($this->target) || is_array($this->target))
        {
            return $this->target;
        }

        if (is_string($this->target) && strpos($this->target, self::CONTROLLER_SEPARATOR) === false)
        {
            //Middleware
            return Container::get($this->target);
        }

        return function (ServerRequestInterface $request) use ($core)
        {
            return $this->callAction($request, $core);
        };
    }

    /**
     * Execute controller action resolved via provided string target.
     *
     * @param ServerRequestInterface $request
     * @param CoreInterface          $core
     * @return mixed
     */
    protected function callAction(ServerRequestInterface $request, CoreInterface $core)
    {
        $target = interpolate($this->target, $this->matches, '<', '>');
        list($controller, $action) = explode(self::CONTROLLER_SEPARATOR, $target);

        //Fixing controller name
        if (($name = strrpos($controller, '\\')) !== false)
        {
            //Fixing controller name
            $controller = substr($controller, 0, $name) . '\\' . ucfirst(substr($controller, $name + 1));
        }

        return $core->callAction($controller, $action, $this->matches);
    }

    /**
     * Create URL using route parameters (will be merged with default values), route pattern and base
     * path.
     *
     * @param array  $parameters
     * @param string $basePath
     * @return string
     */
    public function buildURL(array $parameters = array(), $basePath = '/')
    {
        if (empty($this->compiled))
        {
            $this->compileRoute();
        }

        $parameters += $this->defaults + array_fill_keys($this->compiled['options'], null);

        //Cleaning all bad symbols
        $parameters = array_map(array('Spiral\Helpers\UrlHelper', 'convert'), $parameters);

        //Rendering URL
        $url = interpolate($this->compiled['template'], $parameters, '<', '>');

        return $basePath . trim(str_replace('//', '/', $url), '/');
    }
}
