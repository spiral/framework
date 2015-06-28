<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Facade;

/**
 * @method static string defaultNamespace()
 * @method static string cacheDirectory()
 * @method static array|null getNamespaces()
 * @method static array setNamespaces(array $namespaces)
 * @method static ProcessorInterface getProcessor(string $name)
 * @method static array getProcessors()
 * @method static string staticVariable(string $name, string $value = null)
 * @method static string cachedFilename(string $namespace, string $view)
 * @method static string findView(string $namespace, string $view)
 * @method static bool isExpired(string $filename, string $namespace, string $view)
 * @method static string getFilename(string $namespace, string $view, bool $process = true, bool $resetCache = false)
 * @method static View get(string $view, array $data = array())
 * @method static string render(string $view, array $data = array())
 * @method static string getAlias()
 * @method static ViewManager make(array $parameters = array())
 * @method static ViewManager getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 * @method static setDispatcher(DispatcherInterface $dispatcher = null)
 * @method static DispatcherInterface dispatcher()
 * @method static mixed event(string $event, mixed $context = null)
 */
class View extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'view';
}