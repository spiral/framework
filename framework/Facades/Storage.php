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
use Spiral\Components\Storage\ServerInterface;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageObject;
use Spiral\Core\Facade;

/**
 * @method static StorageContainer registerContainer(string $name, string $prefix, string $server, array $options = array())
 * @method static StorageContainer container(string $container)
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
 * @method static StorageContainer locateContainer(string $address, string $name = null)
 * @method static ServerInterface server(string $server, array $options = array())
 * @method static StorageObject|bool create(StorageContainer $container, string $name, string $filename = '')
 * @method static StorageObject open(string $address)
 * @method static string getAlias()
 * @method static StorageManager make(array $parameters = array())
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface logger()
 * @method static StorageManager getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class Storage extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'storage';
}