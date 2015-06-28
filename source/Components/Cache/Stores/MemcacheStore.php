<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Cache\Stores;

use Spiral\Components\Cache\CacheException;
use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\CacheStore;
use Memcache as MemcacheDriver;
use Memcached as MemcachedDriver;

class MemcacheStore extends CacheStore
{
    /**
     * Internal store name.
     */
    const STORE = 'memcache';

    /**
     * Default store options.
     *
     * @var array
     */
    protected $options = [
        'prefix'        => 'spiral',
        'options'       => [],
        'defaultServer' => [
            'host'       => 'localhost',
            'port'       => 11211,
            'persistent' => true,
            'weight'     => 1
        ]
    ];

    /**
     * Maximum expiration time you can set. http://www.php.net/manual/ru/memcache.set.php
     */
    const MAX_EXPIRATION = 2592000;

    /**
     * Driver type. Adapter will automatically select what driver should be used. However, Memcache
     * is preferred.
     */
    const DRIVER_MEMCACHE  = 1;
    const DRIVER_MEMCACHED = 2;

    /**
     * Current active driver.
     *
     * @var int
     */
    protected $driver = null;

    /**
     * Constructed driver instance.
     *
     * @var MemcacheDriver|MemcachedDriver
     */
    protected $service = null;

    /**
     * List of registered memcache servers.
     *
     * @var array
     */
    protected $servers = [];

    /**
     * Cache prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Create new cache store instance. Every instance should represent a single cache method.
     * Multiple stores can exist at the same time and can be used in different parts of the
     * application.
     *
     * @param CacheManager                  $cache   CacheManager component.
     * @param MemcacheDriver|MemcacheDriver $driver  Pre-created driver instance.
     * @param bool                          $connect If true, custom driver will be connected.
     * @throws CacheException
     */
    public function __construct(CacheManager $cache, $driver = null, $connect = true)
    {
        parent::__construct($cache);

        if (is_object($driver))
        {
            $this->setDriver($driver, $connect);

            return;
        }

        if (!$this->isAvailable())
        {
            return;
        }

        $this->prefix = !empty($this->options['prefix']) ? $this->options['prefix'] . ':' : '';

        if (empty($this->options['servers']))
        {
            throw new CacheException(
                'Unable to create Memcache cache store. A server must be specified.'
            );
        }

        if ($this->driver == self::DRIVER_MEMCACHE)
        {
            $this->service = new MemcacheDriver();
        }
        else
        {
            $this->service = new MemcacheDriver();
        }

        $this->connect();
    }

    /**
     * Set pre-created Memcache driver.
     *
     * @param MemcacheDriver|MemcacheDriver $driver  Pre-created driver instance.
     * @param bool                          $connect If true, custom driver will be connected.
     */
    protected function setDriver($driver, $connect)
    {
        $this->service = $driver;

        $this->driver = $driver instanceof \Memcache
            ? self::DRIVER_MEMCACHE
            : self::DRIVER_MEMCACHED;

        if ($connect)
        {
            $this->connect();
        }
    }

    /**
     * Configure Memcache or Memcached instance with server details.
     */
    protected function connect()
    {
        if ($this->driver == self::DRIVER_MEMCACHE)
        {
            foreach ($this->options['servers'] as $server)
            {
                //Fill some parameters with default values
                $server = array_merge($this->options['defaultServer'], $server);
                $this->service->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persistent'],
                    $server['weight']
                );
            }
        }
        else
        {
            foreach ($this->options['options'] as $option => $value)
            {
                $this->service->setOption($option, $value);
            }

            foreach ($this->options['servers'] as $server)
            {
                //Fill some parameters with default values
                $server = array_merge($this->options['defaultServer'], $server);
                $this->service->addServer(
                    $server['host'],
                    $server['port'],
                    $server['weight']
                );
            }
        }
    }

    /**
     * Check if store is working properly. Should check to see if the store drives does exists,
     * the files are writable, etc.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (class_exists('Memcache', false))
        {
            $this->driver = self::DRIVER_MEMCACHE;

            return true;
        }

        if (class_exists('Memcached', false))
        {
            $this->driver = self::DRIVER_MEMCACHED;

            return true;
        }

        return (bool)$this->driver;
    }

    /**
     * Check if value is presented in cache.
     *
     * @param string $name Stored value name.
     * @return bool
     */
    public function has($name)
    {
        if ($this->service->get($this->prefix . $name) === false)
        {
            if ($this->driver == self::DRIVER_MEMCACHED)
            {
                return $this->service->getResultCode() != \Memcached::RES_NOTFOUND;
            }

            return false;
        }

        return true;
    }

    /**
     * Get value stored in cache or false.
     *
     * @param string $name Stored value name.
     * @return mixed | bool false
     */
    public function get($name)
    {
        return $this->service->get($this->prefix . $name);
    }

    /**
     * Set data in cache. Should automatically create a record if it wasn't created previously or
     * replace an existing record.
     *
     * @param string $name     Stored value name.
     * @param mixed  $data     Data in string or binary format.
     * @param int    $lifetime Duration in seconds until the value expires.
     * @return mixed
     */
    public function set($name, $data, $lifetime)
    {
        $lifetime = min(self::MAX_EXPIRATION + time(), $lifetime + time());
        if ($lifetime < 0)
        {
            $lifetime = 0;
        }

        try
        {
            if ($this->driver == self::DRIVER_MEMCACHE)
            {
                return $this->service->set($this->prefix . $name, $data, 0, $lifetime);
            }
            else
            {
                return $this->service->set($this->prefix . $name, $data, $lifetime);
            }
        }
        catch (\ErrorException $e)
        {
            return false;
        }
    }

    /**
     * Store value in cache with an infinite lifetime. Value should expire only after cache is
     * flushed.
     *
     * @param string $name Stored value name.
     * @param mixed  $data Data in string or binary format.
     * @return mixed
     */
    public function forever($name, $data)
    {
        return $this->service->set($this->prefix . $name, $data);
    }

    /**
     * Delete data from cache.
     *
     * @param string $name Stored value name.
     */
    public function delete($name)
    {
        $this->service->delete($this->prefix . $name);
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
        return $this->service->increment($this->prefix . $name, $delta);
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
        return $this->service->decrement($this->prefix . $name, $delta);
    }

    /**
     * Flush all values stored in cache.
     *
     * @return mixed
     */
    public function flush()
    {
        $this->service->flush();
    }

    /**
     * Retrieve the currently selected memcache driver instance.
     *
     * @return MemcachedDriver|MemcacheDriver
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * The connection to the memcache server will be closed, if the connection wasn't specified as
     * persistent.
     */
    public function __destruct()
    {
        if (!$this->service)
        {
            return;
        }

        if ($this->options['defaultServer']['persistent'] && $this->driver == self::DRIVER_MEMCACHE)
        {
            $this->service->close();
        }

        $this->service = null;
    }
}