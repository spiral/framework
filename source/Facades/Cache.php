<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\CacheStore;
use Spiral\Core\Container;
use Spiral\Core\Facade;

/**
 * @method static mixed storeOptions($adapter)
 * @method static CacheStore store($store = null, array $options = array())
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter, Container $container)
 * @method static CacheManager make($parameters = array(), Container $container = null)
 * @method static CacheManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)

 */
class Cache extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cache';
}