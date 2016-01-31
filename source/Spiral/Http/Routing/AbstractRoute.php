<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\MiddlewareInterface;
use Spiral\Http\MiddlewarePipeline;
use Spiral\Http\Uri;

/**
 * Base for all spiral routes.
 *
 * Routing format (examples given in context of Core->bootstrap() method and Route):
 *
 * Static routes.
 * $this->http->route('profile-<id>', 'Controllers\UserController::showProfile');
 *
 * Dynamic actions:
 * $this->http->route('account/<action>', 'Controllers\AccountController::<action>');
 *
 * Optional segments:
 * $this->http->route('profile[/<id>]', 'Controllers\UserController::showProfile');
 *
 * This route will react on URL's like /profile/ and /profile/someSegment/
 *
 * To determinate your own pattern for segment use construction <segmentName:pattern>
 * $this->http->route('profile[/<id:\d+>]', 'Controllers\UserController::showProfile');
 *
 * Will react only on /profile/ and /profile/1384978/
 *
 * You can use custom pattern for controller and action segments.
 * $this->http->route('users[/<action:edit|save|open>]', 'Controllers\UserController::<action>');
 *
 * Routes can be applied to URI host.
 * $this->http->route(
 *      '<username>.domain.com[/<action>[/<id>]]',
 *      'Controllers\UserController::<action>'
 * )->useHost();
 *
 * Routes can be used non only with controllers (no idea why you may need it):
 * $this->http->route('users', function () {
 *      return "This is users route.";
 * });
 */
abstract class AbstractRoute implements RouteInterface
{
    /**
     * Default segment pattern, this patter can be applied to controller names, actions and etc.
     */
    const DEFAULT_SEGMENT = '[^\.\/]+';

    /**
     * To execute actions.
     *
     * @invisible
     * @var CoreInterface
     */
    protected $core = null;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * Route pattern includes simplified regular expressing later compiled to real regexp.
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * Default set of values to fill route matches and target pattern (if specified as pattern).
     *
     * @var array
     */
    protected $defaults = [];

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
    protected $compiled = [];

    /**
     * Route matches, populated after match() method executed. Internal.
     *
     * @todo not sure if it's good idea to store matches in route?
     * @var array
     */
    protected $matches = [];

    /**
     * @param CoreInterface $core
     * @return $this
     */
    public function setCore(CoreInterface $core)
    {
        $this->core = $core;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($name, array $defaults)
    {
        $copy = clone $this;
        $copy->name = (string)$name;
        $copy->defaults($defaults);

        return $copy;
    }

    /**
     * {@inheritdoc}
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
     * If true (default) route will be matched against path + URI host.
     *
     * @param bool $withHost
     * @return $this
     */
    public function matchHost($withHost = true)
    {
        $this->withHost = $withHost;

        return $this;
    }

    /**
     * Update route defaults (new values will be merged with existed data).
     *
     * @param array $defaults
     * @return $this
     */
    public function defaults(array $defaults)
    {
        $this->defaults = $defaults + $this->defaults;

        return $this;
    }

    /**
     * Associated middleware with route.
     *
     * Example:
     * $route->with(new CacheMiddleware(100));
     * $route->with(ProxyMiddleware::class);
     * $route->with([ProxyMiddleware::class, OtherMiddleware::class]);
     *
     * @param callable|MiddlewareInterface|array $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match(Request $request, $basePath = '/')
    {
        if (empty($this->compiled)) {
            $this->compile();
        }

        $path = $request->getUri()->getPath();
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($this->withHost) {
            $uri = $request->getUri()->getHost() . $path;
        } else {
            $uri = substr($path, strlen($basePath));
        }

        if (preg_match($this->compiled['pattern'], rtrim($uri, '/'), $this->matches)) {
            //To get only named matches
            $this->matches = array_intersect_key($this->matches, $this->compiled['options']);
            $this->matches = array_merge(
                $this->compiled['options'],
                $this->defaults,
                $this->matches
            );

            //todo: return clone this, see copy?
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function perform(Request $request, Response $response, ContainerInterface $container)
    {
        $pipeline = new MiddlewarePipeline($this->middlewares, $container);

        return $pipeline->target($this->createEndpoint($container))->run($request, $response);
    }

    /**
     * {@inheritdoc}
     *
     * @todo need improvement
     */
    public function uri(
        $parameters = [],
        $basePath = '/',
        SlugifyInterface $slugify = null
    ) {
        if (empty($this->compiled)) {
            $this->compile();
        }

        if (empty($slugify)) {
            $slugify = new Slugify();
        }

        $parameters = $this->fetchParameters($parameters, $slugify);
        $parameters = $parameters + $this->matches + $this->defaults + $this->compiled['options'];

        //Uri without empty blocks (pretty stupid implementation)
        $path = strtr(
            \Spiral\interpolate($this->compiled['template'], $parameters, '<', '>'),
            ['[]' => '', '[/]' => '', '[' => '', ']' => '', '//' => '/']
        );

        $uri = new Uri(
            ($this->withHost ? '' : $basePath) . rtrim($path, '/')
        );

        //Getting additional query parameters
        if (!empty($queryParameters = array_diff_key($parameters, $this->compiled['options']))) {
            $uri = $uri->withQuery(http_build_query($queryParameters));
        }

        return $uri;
    }

    /**
     * Generate parameters list.
     *
     * @param \Traversable|array $parameters
     * @param SlugifyInterface   $slugify
     * @return array
     */
    protected function fetchParameters($parameters, SlugifyInterface $slugify)
    {
        $result = [];
        $allowed = array_keys($this->compiled['options']);

        foreach ($parameters as $key => $parameter) {
            if (!array_key_exists($key, $this->compiled['options'])) {
                //Numeric key?
                if (isset($allowed[$key])) {
                    $key = $allowed[$key];
                } else {
                    continue;
                }
            }

            if (is_string($parameter) && !preg_match('/^[a-z\-_0-9]+$/i', $parameter)) {
                //Default Slugify is pretty slow, we'd better not apply it for every value
                $result[$key] = $slugify->slugify($parameter);
                continue;
            }

            $result[$key] = (string)$parameter;
        }

        return $result;
    }

    /**
     * Create callable route endpoint.
     *
     * @param ContainerInterface $container
     * @return callable
     */
    abstract protected function createEndpoint(ContainerInterface $container);

    /**
     * Internal helper used to create execute controller action using associated core instance.
     *
     * @param ContainerInterface $container
     * @param string             $controller
     * @param string             $action
     * @param array              $parameters
     * @return mixed
     * @throws ClientException
     */
    protected function callAction(
        ContainerInterface $container,
        $controller,
        $action,
        array $parameters = []
    ) {
        if (empty($this->core)) {
            $this->core = $container->get(CoreInterface::class);
        }

        try {
            return $this->core->callAction($controller, $action, $parameters);
        } catch (ControllerException $e) {
            throw $this->convertException($e);
        }
    }

    /**
     * Converts controller exceptions into client exceptions.
     *
     * @param ControllerException $exception
     * @return ClientException
     */
    protected function convertException(ControllerException $exception)
    {
        switch ($exception->getCode()) {
            case ControllerException::BAD_ACTION:
            case ControllerException::NOT_FOUND:
                return new ClientException(ClientException::NOT_FOUND, $exception->getMessage());
            case  ControllerException::FORBIDDEN:
                return new ClientException(ClientException::FORBIDDEN, $exception->getMessage());
            default:
                return new ClientException(ClientException::BAD_DATA, $exception->getMessage());
        }
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
}
