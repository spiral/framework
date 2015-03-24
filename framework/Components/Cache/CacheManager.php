<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Cache;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;

class CacheManager extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'cache';

    /**
     * Already constructed cache adapters.
     *
     * @var CacheStore[]
     */
    protected $stores = false;

    /**
     * Constructing CacheManager and selecting default adapter.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->config = $core->loadConfig('cache');
    }

    /**
     * Adapter specified options.
     *
     * @param string $adapter
     * @return mixed
     */
    public function storeOptions($adapter)
    {
        return $this->config['stores'][$adapter];
    }

    /**
     * Will return specified or default cache adapter. This function will load cache adapter if it
     * wasn't initiated, or fetch it from memory.
     *
     * @param string $store   Keep null, empty or not specified to get default cache adapter.
     * @param array  $options Custom store options to set or replace.
     * @return CacheStore
     * @throws CacheException
     */
    public function store($store = null, array $options = array())
    {
        $store = $store ?: $this->config['store'];

        if (isset($this->stores[$store]))
        {
            return $this->stores[$store];
        }

        if (!empty($options))
        {
            $this->config['stores'][$store] = $options;
        }

        benchmark('cache::store', $store);
        $this->stores[$store] = Container::get(
            $this->config['stores'][$store]['class'],
            array('cache' => $this),
            null,
            true
        );
        benchmark('cache::store', $store);

        if ($store == $this->config['store'] && !$this->stores[$store]->isAvailable())
        {
            throw new CacheException(
                "Unable to use default store '{$store}', driver is unavailable."
            );
        }

        return $this->stores[$store];
    }

    /**
     * InjectionManager will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare dependency.
     *
     * This method can return pre-defined instance or create new one based on requested class, parameter
     * reflection can be used to dynamic class constructing, for example it can define database name
     * or config section should be used to construct requested instance.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionParameter $parameter
     * @return mixed
     */
    public static function resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
    {
        if (!$class->isInstantiable())
        {
            return self::getInstance()->store();
        }

        return Container::get($class->getName(), array(
            'cache' => self::getInstance()
        ), null, true);
    }
}
