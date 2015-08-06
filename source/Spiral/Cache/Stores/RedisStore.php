<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Cache\Stores;

use Spiral\Cache\CacheProvider;
use Spiral\Cache\CacheStore;
use Spiral\Redis\RedisClient;
use Spiral\Redis\RedisManager;

/**
 * Talks to redis database.
 */
class RedisStore extends CacheStore
{
    /**
     * {@inheritdoc}
     */
    const STORE = 'redis';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        'client' => 'default',
        'prefix' => 'spiral:'
    ];

    /**
     * @var RedisClient
     */
    protected $client = null;

    /**
     * {@inheritdoc}
     *
     * @param CacheProvider $cache CacheManager component.
     * @param RedisManager  $redis RedisManager component.
     */
    public function __construct(CacheProvider $cache, RedisManager $redis)
    {
        parent::__construct($cache);
        $this->client = $redis->client($this->options['client']);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return $this->options['enabled'];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->client->exists($this->prefix . $name);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (is_null($data = $this->client->get($this->prefix . $name))) {
            return null;
        }

        return is_numeric($data) ? $data : unserialize($data);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function forever($name, $data)
    {
        $this->client->set($this->prefix . $name, is_numeric($data) ? $data : serialize($data));
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $this->client->del($this->prefix . $name);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($name, $delta = 1)
    {
        return $this->client->incrby($this->prefix . $name, $delta);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($name, $delta = 1)
    {
        return $this->client->decrby($this->prefix . $name, $delta);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->client->flushDB();
    }
}