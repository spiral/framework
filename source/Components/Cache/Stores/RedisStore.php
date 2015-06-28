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
use Spiral\Components\Redis\RedisClient;
use Spiral\Components\Redis\RedisManager;

class RedisStore extends CacheStore
{
    /**
     * Internal store name.
     */
    const STORE = 'redis';

    /**
     * Default store options.
     *
     * @var array
     */
    protected $options = array(
        'client' => 'default',
        'prefix' => 'spiral'
    );

    /**
     * Redis client used for cache operations.
     *
     * @var RedisClient
     */
    protected $client = null;

    /**
     * Prefix to be used for every key created in redis database using the cache store.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Create a new cache store instance. Every instance should represent a single cache method.
     * Multiple stores can exist at the same time and be used in different parts of the application.
     *
     * @param CacheManager $cache CacheManager component.
     * @param RedisManager $redis RedisManager component.
     */
    public function __construct(CacheManager $cache, RedisManager $redis = null)
    {
        parent::__construct($cache);

        if (empty($redis))
        {
            $redis = RedisManager::getInstance();
        }

        $this->client = $redis->client($this->options['client']);
        $this->prefix = !empty($this->options['prefix']) ? $this->options['prefix'] . ':' : '';
    }

    /**
     * Check if store is working properly. Should check if the store drives still exists, files are
     * writable, etc.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->options['enabled'];
    }

    /**
     * Check if a value is presented in cache.
     *
     * @param string $name Stored value name.
     * @return bool
     */
    public function has($name)
    {
        return $this->client->exists($this->prefix . $name);
    }

    /**
     * Get value stored in cache.
     *
     * @param string $name Stored value name.
     * @return mixed
     */
    public function get($name)
    {
        if (is_null($data = $this->client->get($this->prefix . $name)))
        {
            return null;
        }

        return is_numeric($data) ? $data : unserialize($data);
    }

    /**
     * Set data in cache. Should automatically create a record if it wasn't created before or
     * replace an existing record.
     *
     * @param string $name     Stored value name.
     * @param mixed  $data     Data in string or binary format.
     * @param int    $lifetime Duration in seconds until value will expire.
     * @return mixed
     */
    public function set($name, $data, $lifetime)
    {
        $this->client->setex(
            $this->prefix . $name,
            $lifetime,
            is_numeric($data) ? $data : serialize($data)
        );
    }

    /**
     * Store value in cache with an infinite lifetime. Value should expire only when cache is
     * flushed.
     *
     * @param string $name Stored value name.
     * @param mixed  $data Data in string or binary format.
     * @return mixed
     */
    public function forever($name, $data)
    {
        $this->client->set(
            $this->prefix . $name,
            is_numeric($data) ? $data : serialize($data)
        );
    }

    /**
     * Delete data from cache. Name will be attached to applicationID to prevent run ins.
     *
     * @param string $name Stored value name.
     */
    public function delete($name)
    {
        $this->client->del($this->prefix . $name);
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
        return $this->client->incrby($this->prefix . $name, $delta);
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
        return $this->client->decrby($this->prefix . $name, $delta);
    }

    /**
     * Flush all values stored in cache.
     *
     * @return mixed
     */
    public function flush()
    {
        $this->client->flushDB();
    }
}