<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\StoreInterface;
use Spiral\Core\Container;
use Spiral\Core\Proxy;

/**
 * @method static mixed storeOptions($adapter)
 * @method static StoreInterface store($store = null, array $options = [])
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter, Container $container)
 * @method static CacheManager make($parameters = [], Container $container = null)
 * @method static CacheManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)

 */
class Cache extends Proxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cache';
}