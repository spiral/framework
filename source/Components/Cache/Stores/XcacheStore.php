<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Cache\Stores;

use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\CacheStore;

class XcacheStore extends CacheStore
{
    /**
     * Internal store name.
     */
    const STORE = 'xcache';

    /**
     * Default store options.
     *
     * @var array
     */
    protected $options = [
        'prefix' => 'spiral'
    ];

    /**
     * Cache prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Create a new cache store instance. Every instance should represent a single cache method.
     * Multiple stores can exist at the same time and be used in different parts of the application.
     *
     * @param CacheManager $cache CacheManager component.
     */
    public function __construct(CacheManager $cache)
    {
        parent::__construct($cache);
        $this->prefix = !empty($this->options['prefix']) ? $this->options['prefix'] . ':' : '';
    }

    /**
     * Check if store is working properly. Should check if the store drives does exist, files are
     * writable, etc.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return extension_loaded('xcache');
    }

    /**
     * Check if a value is present in cache.
     *
     * @param string $name Stored value name.
     * @return bool
     */
    public function has($name)
    {
        return xcache_isset($this->prefix . $name);
    }

    /**
     * Get value stored in cache.
     *
     * @param string $name Stored value name.
     * @return mixed
     */
    public function get($name)
    {
        return xcache_get($this->prefix . $name);
    }

    /**
     * Set data in cache, should automatically create record if it wasn't created before or replace
     * already existed record.
     *
     * @param string $name     Stored value name.
     * @param mixed  $data     Data in string or binary format.
     * @param int    $lifetime Duration in seconds till value will expire.
     * @return mixed
     */
    public function set($name, $data, $lifetime)
    {
        return xcache_set($this->prefix . $name, $data, $lifetime);
    }

    /**
     * Store value in cache with infinite lifetime. Value will expire only when cache is flushed.
     *
     * @param string $name Stored value name.
     * @param mixed  $data Data in string or binary format.
     * @return mixed
     */
    public function forever($name, $data)
    {
        return xcache_set($this->prefix . $name, $data, 0);
    }

    /**
     * Delete data from cache.
     *
     * @param string $name Stored value name.
     */
    public function delete($name)
    {
        xcache_unset($this->prefix . $name);
    }

    /**
     * Increment numeric value stored in cache.
     *
     * @param string $name  Stored value name.
     * @param int    $delta How much to increment by. 1 by default.
     * @return mixed
     */
    public function increment($name, $delta = 1)
    {
        return xcache_inc($this->prefix . $name, $delta);
    }

    /**
     * Decrement numeric value stored in cache.
     *
     * @param string $name  Stored value name.
     * @param int    $delta How much to decrement by. 1 by default.
     * @return mixed
     */
    public function decrement($name, $delta = 1)
    {
        return xcache_dec($this->prefix . $name, $delta);
    }

    /**
     * Flush all values stored in cache.
     *
     * @return mixed
     */
    public function flush()
    {
        xcache_clear_cache(XC_TYPE_VAR);
    }
}