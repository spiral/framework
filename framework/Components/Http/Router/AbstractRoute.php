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
use Spiral\Core\Container;

abstract class AbstractRoute implements RouteInterface
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
     * Declared route name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Middlewares associated with route. Can contain middleware aliases provided from top Router
     * or real middleware instances. You can always get access to parent route using route attribute
     * of server request.
     *
     * @var array
     */
    protected $middlewares = array();

    /**
     * Route pattern includes simplified regular expressing later compiled to real regexp. Pattern
     * with be applied to URI path with excluded active path value (to make routes work when application
     * located in folder and etc).
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * List of methods route should react to, by default all methods are passed.
     *
     * @var array
     */
    protected $methods = array();

    /**
     * Default set of values to fill route matches and target pattern (if specified as pattern).
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * If true route will be matched with URI host in addition to path. BasePath will be ignored.
     *
     * @var bool
     */
    protected $withHost = false;

    /**
     * Compiled route options, pattern and etc. Internal data.
     *
     * @invisible
     * @var array
     */
    protected $compiled = array();

    /**
     * Result of regular expression. Matched can be used to fill target controller pattern or send
     * to controller method as arguments.
     *
     * @invisible
     * @var array
     */
    protected $matches = array();

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
     * Set route name. This action should be performed BEFORE parent router will be created, in other
     * scenario route will be available under old name.
     *
     * @param string $name
     * @return static
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * If true (default) route will be matched against path + URI host.
     *
     * @param bool $withHost
     * @return static
     */
    public function withHost($withHost = true)
    {
        $this->withHost = $withHost;

        return $this;
    }

    /**
     * List of methods route should react to, by default all methods are passed.
     *
     * Example:
     * $route->only('GET');
     * $route->only(['POST', 'PUT']);
     *
     * @param array|string $method
     * @return static
     */
    public function only($method)
    {
        $this->methods = is_array($method) ? $method : func_get_args();

        return $this;
    }

    /**
     * Set default values (will be merged with current default) to be used in generated target.
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
     * Associated inner middleware with route. Middleware will be executed "at top" of real route
     * target such as controller. Attention, response provided from inner not necessary will be
     * type of ResponseInterface as real response wrapping will happen on higher HttpDispatcher
     * level.
     *
     * Route can use middlewares previously registered in Route by it's aliases.
     *
     * Example:
     *
     * $router->registerMiddleware('cache', new CacheMiddleware(100));
     * $route->with('cache');
     *
     * @param string|MiddlewareInterface|\Closure $middleware Inner middleware alias, instance or
     *                                                        closure.
     * @return static
     */
    public function with($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Helper method used to compile simplified route pattern to valid regular expression.
     *
     * We can cache results of this method in future.
     */
    protected function compile()
    {
        $replaces = array(
            '/' => '\\/',
            '(' => '(?:',
            ')' => ')?',
            '.' => '\.'
        );

        $options = array();
        if (preg_match_all('/<(\w+):?(.*?)?>/', $this->pattern, $matches))
        {
            $variables = array_combine($matches[1], $matches[2]);
            foreach ($variables as $name => $segment)
            {
                $segment = $segment ?: self::DEFAULT_SEGMENT;
                $replaces["<$name>"] = "(?P<$name>$segment)";
                $options[] = $name;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $this->pattern);
        $template = stripslashes(str_replace(
            array(')', '(', '?'),
            '',
            $template
        ));

        $this->compiled = array(
            'pattern'  => '/^' . strtr($template, $replaces) . '$/u',
            'template' => $template,
            'options'  => $options
        );
    }

    /**
     * Check if route matched with provided request. Will check url pattern and pre-conditions.
     *
     * @param ServerRequestInterface $request
     * @param string                 $basePath
     * @return bool
     */
    public function match(ServerRequestInterface $request, $basePath = '/')
    {
        if (!empty($this->methods) && !in_array($request->getMethod(), $this->methods))
        {
            return false;
        }

        if (empty($this->compiled))
        {
            $this->compile();
        }

        $path = $request->getUri()->getPath();
        if (empty($path) || $path[0] !== '/')
        {
            $path = '/' . $path;
        }

        if ($this->withHost)
        {
            $uri = $request->getUri()->getHost() . $path;
        }
        else
        {
            $uri = substr($path, strlen($basePath));
        }

        if (preg_match($this->compiled['pattern'], rtrim($uri, '/'), $this->matches))
        {
            $this->matches = array_merge(
                $this->matches,
                $this->defaults,
                array_fill_keys($this->compiled['options'], null)
            );

            return true;
        }

        return false;
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
     * Create route specific request pipeline.
     *
     * @param Container $container
     * @param array     $middlewaresAliases
     * @return MiddlewarePipe
     */
    protected function getPipeline(Container $container, array $middlewaresAliases = array())
    {
        $middlewares = array();
        foreach ($this->middlewares as $middleware)
        {
            //Resolving middleware aliases
            $middlewares[] = isset($middlewareAliases[$middleware])
                ? $middlewareAliases[$middleware]
                : $middleware;
        }

        return new MiddlewarePipe($container, $middlewares);
    }

    /**
     * Create URL using route parameters (will be merged with default values), route pattern and base
     * path.
     *
     * @param array  $parameters
     * @param string $basePath
     * @return string
     */
    public function createURL(array $parameters = array(), $basePath = '/')
    {
        if (empty($this->compiled))
        {
            $this->compile();
        }

        $parameters = array_merge(
            $parameters,
            $this->defaults,
            array_fill_keys($this->compiled['options'], null)
        );

        //Cleaning all bad symbols
        $parameters = array_map(array('Spiral\Helpers\UrlHelper', 'slug'), $parameters);

        //Rendering URL
        $url = interpolate($this->compiled['template'], $parameters, '<', '>');

        return $basePath . trim(str_replace('//', '/', $url), '/');
    }
}