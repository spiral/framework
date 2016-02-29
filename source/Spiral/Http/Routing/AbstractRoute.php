<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Exceptions\RouteException;
use Spiral\Http\MiddlewareInterface;
use Spiral\Http\MiddlewarePipeline;
use Spiral\Http\Uri;
use Spiral\Support\Strings;

/**
 * Abstract route with ability to execute endpoint using middleware pipeline and context container.
 *
 * Attention, route does not extends container is it's mandatory to be set.
 */
abstract class AbstractRoute implements RouteInterface
{
    /**
     * Default segment pattern, this patter can be applied to controller names, actions and etc.
     */
    const DEFAULT_SEGMENT = '[^\.\/]+';

    /**
     * @var string
     */
    private $name = '';

    /**
     * Path prefix (base path).
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Default set of values to fill route matches and target pattern (if specified as pattern).
     *
     * @var array
     */
    private $defaults = [];

    /**
     * If true route will be matched with URI host in addition to path. BasePath will be ignored.
     *
     * @var bool
     */
    private $withHost = false;

    /**
     * Route matches, populated after match() method executed. Internal.
     *
     * @var array
     */
    private $matches = [];

    /**
     * Compiled route options, pattern and etc. Internal data.
     *
     * @invisible
     * @var array
     */
    private $compiled = [];

    /**
     * Route pattern includes simplified regular expressing later compiled to real regexp.
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * Route endpoint container context.
     *
     * @invisible
     * @var ContainerInterface|null
     */
    protected $container = null;

    /**
     * @param string $name
     * @param array  $defaults
     */
    public function __construct($name, array $defaults)
    {
        $this->name = $name;
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function withContainer(ContainerInterface $container)
    {
        $route = clone $this;
        $route->container = $container;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function withName($name)
    {
        $route = clone $this;
        $route->name = $name;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function withPrefix($prefix)
    {
        $route = clone $this;
        $route->prefix = $prefix;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * If true (default) route will be matched against path + URI host. Returns new route instance.
     *
     * @param bool $withHost
     * @return self
     */
    public function withHost($withHost = true)
    {
        $route = clone $this;
        $route->withHost = $withHost;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function withDefaults(array $defaults)
    {
        $copy = clone $this;
        $copy->defaults = $defaults;

        return $copy;
    }

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Associated middleware with route. New instance of route will be returned.
     *
     * Example:
     * $route->withMiddleware(new CacheMiddleware(100));
     * $route->withMiddleware(ProxyMiddleware::class);
     * $route->withMiddleware([ProxyMiddleware::class, OtherMiddleware::class]);
     *
     * @param callable|MiddlewareInterface|array $middleware
     * @return self
     */
    public function withMiddleware($middleware)
    {
        $route = clone $this;
        if (is_array($middleware)) {
            $route->middlewares = array_merge($route->middlewares, $middleware);
        } else {
            $route->middlewares[] = $middleware;
        }

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function match(Request $request)
    {
        if (empty($this->compiled)) {
            $this->compile();
        }

        if (preg_match($this->compiled['pattern'], $this->getSubject($request), $matches)) {

            //To get only named matches
            $matches = array_intersect_key($matches, $this->compiled['options']);
            $matches = array_merge($this->compiled['options'], $this->defaults, $matches);

            $route = clone $this;
            $route->matches = $matches;

            return $route;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function perform(Request $request, Response $response)
    {
        if (empty($this->container)) {
            throw new RouteException("Unable to perform route endpoint without given container");
        }

        $pipeline = new MiddlewarePipeline($this->middlewares, $this->container);

        return $pipeline->target($this->createEndpoint())->run($request, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function uri($parameters = [])
    {
        if (empty($this->compiled)) {
            $this->compile();
        }

        $parameters = array_merge(
            $this->compiled['options'],
            $this->defaults,
            $this->matches,
            $this->fetchSegments($parameters, $query)
        );

        //Uri without empty blocks (pretty stupid implementation)
        $path = strtr(
            \Spiral\interpolate($this->compiled['template'], $parameters, '<', '>'),
            ['[]' => '', '[/]' => '', '[' => '', ']' => '', '//' => '/']
        );

        $uri = new Uri(($this->withHost ? '' : $this->prefix) . rtrim($path, '/'));

        return empty($query) ? $uri : $uri->withQuery(http_build_query($query));
    }

    /**
     * Route matches.
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getMatch($name, $default = null)
    {
        if (array_key_exists($name, $this->matches)) {
            return $this->matches[$name];
        }

        return $default;
    }

    /**
     * Fetch uri segments and query parameters.
     *
     * @param \Traversable|array $parameters
     * @param array              $query Query parameters.
     * @return array
     */
    protected function fetchSegments($parameters, &$query)
    {
        $allowed = array_keys($this->compiled['options']);

        $result = [];
        foreach ($parameters as $key => $parameter) {
            if (is_numeric($key) && isset($allowed[$key])) {
                $key = $allowed[$key];
            } elseif (
                !array_key_exists($key, $this->compiled['options'])
                && is_array($parameters)
            ) {
                $query[$key] = $parameters;
                continue;
            }

            if (is_string($parameter) && !preg_match('/^[a-z\-_0-9]+$/i', $parameter)) {
                $result[$key] = Strings::slug($parameter);
                continue;
            }

            $result[$key] = (string)$parameter;
        }

        return $result;
    }


    /**
     * Create callable route endpoint.
     *
     * @return callable
     */
    abstract protected function createEndpoint();

    /**
     * {@inheritdoc}
     */
    protected function container()
    {
        if (empty($this->container)) {
            throw new RouteException("Route context container has not been set");
        }

        return $this->container;
    }

    /**
     * Compile router pattern into valid regexp.
     */
    private function compile()
    {
        $replaces = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];

        $options = [];
        if (preg_match_all('/<(\w+):?(.*?)?>/', $this->pattern, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);

            foreach ($variables as $name => $segment) {
                //Segment regex
                $segment = !empty($segment) ? $segment : self::DEFAULT_SEGMENT;
                $replaces["<$name>"] = "(?P<$name>$segment)";
                $options[] = $name;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $this->pattern);

        $this->compiled = [
            'pattern'  => '/^' . strtr($template, $replaces) . '$/iu',
            'template' => stripslashes(str_replace('?', '', $template)),
            'options'  => array_fill_keys($options, null)
        ];
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getSubject(Request $request)
    {
        $path = $request->getUri()->getPath();

        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($this->withHost) {
            $uri = $request->getUri()->getHost() . $path;
        } else {
            $uri = substr($path, strlen($this->prefix));
        }

        return rtrim($uri, '/');
    }
}
