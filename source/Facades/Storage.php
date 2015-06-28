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
use Spiral\Components\Storage\StorageServerInterface;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageObject;
use Spiral\Core\Container;
use Spiral\Core\Facade;

/**
 * @method static StorageContainer registerContainer($name, $prefix, $server, array $options = array())
 * @method static StorageContainer container($container)
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter, Container $container)
 * @method static StorageContainer locateContainer($address, &$name = null)
 * @method static StorageServerInterface server($server, array $options = array())
 * @method static StorageObject|bool put($container, $name, $origin = '')
 * @method static StorageObject open($address)
 * @method static StorageManager make($parameters = array(), Container $container = null)
 * @method static StorageManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static void setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 */
class Storage extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'storage';
}