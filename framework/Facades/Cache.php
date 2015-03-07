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
use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\CacheStore;
use Spiral\Core\Facade;

/**
 * @method static mixed storeOptions(string $adapter)
 * @method static CacheStore store(string $store = null, array $options = array())
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
 * @method static bool has(string $name)
 * @method static mixed get(string $name)
 * @method static mixed set(string $name, mixed $data, int $lifetime)
 * @method static mixed forever(string $name, mixed $data)
 * @method static delete(string $name)
 * @method static mixed pull(string $name)
 * @method static mixed remember(string $name, int $lifetime, callback $callback)
 * @method static mixed increment(string $name, int $delta = 1)
 * @method static mixed decrement(string $name, int $delta = 1)
 * @method static mixed flush()
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface logger()
 * @method static string getAlias()
 * @method static CacheManager make(array $parameters = array())
 * @method static CacheManager getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class Cache extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class name should be defined
     * in bindedComponent constant.
     */
    const COMPONENT = 'cache';
}