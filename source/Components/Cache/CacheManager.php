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
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;

/**
 * @method bool has(string $name)
 * @method mixed get(string $name)
 * @method mixed set(string $name, mixed $data, int $lifetime)
 * @method mixed forever(string $name, mixed $data)
 * @method delete(string $name)
 * @method mixed pull(string $name)
 * @method mixed remember(string $name, int $lifetime, callback $callback)
 * @method mixed increment(string $name, int $delta = 1)
 * @method mixed decrement(string $name, int $delta = 1)
 * @method mixed flush()
 */
class CacheManager extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Already constructed cache adapters.
     *
     * @var CacheStore[]
     */
    protected $stores = false;

    /**
     * Constructing CacheManager and selecting default adapter.
     *
     * @param ConfiguratorInterface $configurator
     * @param Container             $container
     */
    public function __construct(ConfiguratorInterface $configurator, Container $container)
    {
        $this->config = $configurator->getConfig('cache');
        $this->container = $container;
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
     * @return StoreInterface
     * @throws CacheException
     */
    public function store($store = null, array $options = [])
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
        $this->stores[$store] = $this->container->get(
            $this->config['stores'][$store]['class'],
            ['cache' => $this],
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
     * @param Container            $container
     * @return mixed
     */
    public function resolveInjection(
        \ReflectionClass $class,
        \ReflectionParameter $parameter,
        Container $container
    )
    {
        if (!$class->isInstantiable())
        {
            return $this->store();
        }

        return $container->get($class->getName(), [
            'cache' => $this
        ], null, true);
    }

    /**
     * Bypass call to default store.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments = [])
    {
        return call_user_func_array([$this->store(), $method], $arguments);
    }
}
