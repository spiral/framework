<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Predis\Response\ResponseInterface;
use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\Http\HttpDispatcher;
use Spiral\Components\Http\MiddlewareInterface;
use Spiral\Components\Http\Router\ResourceRoute;
use Spiral\Components\Http\Router\Route;
use Spiral\Components\Http\Router\RouteInterface;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Facade;

/**
 * @method static HttpDispatcher add(string $path, MiddlewareInterface $endpoint)
 * @method static Request|null getRequest()
 * @method static ResponseInterface perform(Request $request)
 * @method static string getAlias()
 * @method static HttpDispatcher make(array $parameters = array())
 * @method static HttpDispatcher getInstance()
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 * @method static setDispatcher(DispatcherInterface $dispatcher = null)
 * @method static DispatcherInterface dispatcher()
 * @method static mixed event(string $event, mixed $context = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static Router getRouter()
 * @method static addRoute(RouteInterface $route)
 * @method static Route route($pattern, null $target = null, array $defaults = array())
 * @method static ResourceRoute resource(string $controller, string $pattern = '')
 */
class Http extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'http';
}