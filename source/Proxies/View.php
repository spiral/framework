<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\View\ViewInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Core\Container;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Proxy;

/**
 * @method static string cacheDirectory()
 * @method static array|null getNamespaces()
 * @method static array setNamespaces(array $namespaces)
 * @method static ViewManager addNamespace($namespace, $directory)
 * @method static string getVariable($name, $default = null)
 * @method static string setVariable($name, $value)
 * @method static string cachedFilename($namespace, $view)
 * @method static string findView($namespace, $view, &$engine = null)
 * @method static string getFilename($namespace, $view, $compile = true, $resetCache = false, &$engine = null)
 * @method static ViewInterface get($view, array $data = [])
 * @method static string render($view, array $data = [])
 * @method static ViewManager make($parameters = [], Container $container = null)
 * @method static ViewManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static void setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 * @method static void setDispatcher(DispatcherInterface $dispatcher = null)
 * @method static DispatcherInterface dispatcher()
 * @method static void on($event, $listener)
 */
class View extends Proxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'view';
}