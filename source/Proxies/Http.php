<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Predis\Response\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\Debug\Snapshot;
use Spiral\Components\Http\HttpDispatcher;
use Spiral\Components\Http\Request;
use Spiral\Components\Http\Router\Route;
use Spiral\Components\Http\Router\RouteInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Proxy;

/**
 * @method static string getBasePath()
 * @method static HttpDispatcher add($path, $endpoint)
 * @method static void start(CoreInterface $core)
 * @method static Request|null getRequest()
 * @method static array|ResponseInterface perform(ServerRequestInterface $request)
 * @method static void dispatch(\Psr\Http\Message\ResponseInterface $response)
 * @method static void handleException(Snapshot $snapshot)
 * @method static HttpDispatcher make($parameters = [], Container $container = null)
 * @method static HttpDispatcher getInstance(Container $container = null)
 * @method static void setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 * @method static void setDispatcher(DispatcherInterface $dispatcher = null)
 * @method static DispatcherInterface dispatcher()
 * @method static void on($event, $listener)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static Router getRouter()
 * @method static void addRoute(RouteInterface $route)
 * @method static Route route($pattern, $target = null, array $defaults = [])
 */
class Http extends Proxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'http';
}