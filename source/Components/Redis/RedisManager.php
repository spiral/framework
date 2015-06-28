<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Redis;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectionManagerInterface;

/**
 * @method mixed del(array $keys)
 * @method mixed dump($key)
 * @method mixed exists($key)
 * @method mixed expire($key, $seconds)
 * @method mixed expireat($key, $timestamp)
 * @method mixed keys($pattern)
 * @method mixed move($key, $db)
 * @method mixed object($subcommand, $key)
 * @method mixed persist($key)
 * @method mixed pexpire($key, $milliseconds)
 * @method mixed pexpireat($key, $timestamp)
 * @method mixed pttl($key)
 * @method mixed randomkey()
 * @method mixed rename($key, $target)
 * @method mixed renamenx($key, $target)
 * @method mixed scan($cursor, array $options = null)
 * @method mixed sort($key, array $options = null)
 * @method mixed ttl($key)
 * @method mixed type($key)
 * @method mixed append($key, $value)
 * @method mixed bitcount($key, $start = null, $end = null)
 * @method mixed bitop($operation, $destkey, $key)
 * @method mixed decr($key)
 * @method mixed decrby($key, $decrement)
 * @method mixed get($key)
 * @method mixed getbit($key, $offset)
 * @method mixed getrange($key, $start, $end)
 * @method mixed getset($key, $value)
 * @method mixed incr($key)
 * @method mixed incrby($key, $increment)
 * @method mixed incrbyfloat($key, $increment)
 * @method mixed mget(array $keys)
 * @method mixed mset(array $dictionary)
 * @method mixed msetnx(array $dictionary)
 * @method mixed psetex($key, $milliseconds, $value)
 * @method mixed set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
 * @method mixed setbit($key, $offset, $value)
 * @method mixed setex($key, $seconds, $value)
 * @method mixed setnx($key, $value)
 * @method mixed setrange($key, $offset, $value)
 * @method mixed strlen($key)
 * @method mixed hdel($key, array $fields)
 * @method mixed hexists($key, $field)
 * @method mixed hget($key, $field)
 * @method mixed hgetall($key)
 * @method mixed hincrby($key, $field, $increment)
 * @method mixed hincrbyfloat($key, $field, $increment)
 * @method mixed hkeys($key)
 * @method mixed hlen($key)
 * @method mixed hmget($key, array $fields)
 * @method mixed hmset($key, array $dictionary)
 * @method mixed hscan($key, $cursor, array $options = null)
 * @method mixed hset($key, $field, $value)
 * @method mixed hsetnx($key, $field, $value)
 * @method mixed hvals($key)
 * @method mixed blpop(array $keys, $timeout)
 * @method mixed brpop(array $keys, $timeout)
 * @method mixed brpoplpush($source, $destination, $timeout)
 * @method mixed lindex($key, $index)
 * @method mixed linsert($key, $whence, $pivot, $value)
 * @method mixed llen($key)
 * @method mixed lpop($key)
 * @method mixed lpush($key, array $values)
 * @method mixed lpushx($key, $value)
 * @method mixed lrange($key, $start, $stop)
 * @method mixed lrem($key, $count, $value)
 * @method mixed lset($key, $index, $value)
 * @method mixed ltrim($key, $start, $stop)
 * @method mixed rpop($key)
 * @method mixed rpoplpush($source, $destination)
 * @method mixed rpush($key, array $values)
 * @method mixed rpushx($key, $value)
 * @method mixed sadd($key, array $members)
 * @method mixed scard($key)
 * @method mixed sdiff(array $keys)
 * @method mixed sdiffstore($destination, array $keys)
 * @method mixed sinter(array $keys)
 * @method mixed sinterstore($destination, array $keys)
 * @method mixed sismember($key, $member)
 * @method mixed smembers($key)
 * @method mixed smove($source, $destination, $member)
 * @method mixed spop($key)
 * @method mixed srandmember($key, $count = null)
 * @method mixed srem($key, $member)
 * @method mixed sscan($key, $cursor, array $options = null)
 * @method mixed sunion(array $keys)
 * @method mixed sunionstore($destination, array $keys)
 * @method mixed zadd($key, array $membersAndScoresDictionary)
 * @method mixed zcard($key)
 * @method mixed zcount($key, $min, $max)
 * @method mixed zincrby($key, $increment, $member)
 * @method mixed zinterstore($destination, array $keys, array $options = null)
 * @method mixed zrange($key, $start, $stop, array $options = null)
 * @method mixed zrangebyscore($key, $min, $max, array $options = null)
 * @method mixed zrank($key, $member)
 * @method mixed zrem($key, $member)
 * @method mixed zremrangebyrank($key, $start, $stop)
 * @method mixed zremrangebyscore($key, $min, $max)
 * @method mixed zrevrange($key, $start, $stop, array $options = null)
 * @method mixed zrevrangebyscore($key, $min, $max, array $options = null)
 * @method mixed zrevrank($key, $member)
 * @method mixed zunionstore($destination, array $keys, array $options = null)
 * @method mixed zscore($key, $member)
 * @method mixed zscan($key, $cursor, array $options = null)
 * @method mixed zrangebylex($key, $start, $stop, array $options = null)
 * @method mixed zremrangebylex($key, $min, $max)
 * @method mixed zlexcount($key, $min, $max)
 * @method mixed pfadd($key, array $elements)
 * @method mixed pfmerge($destinationKey, array $sourceKeys)
 * @method mixed pfcount(array $keys)
 * @method mixed pubsub($subcommand, $argument)
 * @method mixed publish($channel, $message)
 * @method mixed discard()
 * @method mixed exec()
 * @method mixed multi()
 * @method mixed unwatch()
 * @method mixed watch($key)
 * @method mixed eval($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
 * @method mixed evalsha($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
 * @method mixed script($subcommand, $argument = null)
 * @method mixed auth($password)
 * @method mixed echo ($message)
 * @method mixed ping($message = null)
 * @method mixed select($database)
 * @method mixed bgrewriteaof()
 * @method mixed bgsave()
 * @method mixed config($subcommand, $argument = null)
 * @method mixed dbsize()
 * @method mixed flushall()
 * @method mixed flushdb()
 * @method mixed info($section = null)
 * @method mixed lastsave()
 * @method mixed save()
 * @method mixed slaveof($host, $port)
 * @method mixed slowlog($subcommand, $argument = null)
 * @method mixed time()
 */
class RedisManager extends Component implements InjectionManagerInterface
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
     * Copying set of redis constants due same name were used.
     */
    const AFTER = 'after';
    const BEFORE = 'before';
    const OPT_SERIALIZER = 1;
    const OPT_PREFIX = 2;
    const OPT_READ_TIMEOUT = 3;
    const OPT_SCAN = 4;
    const SERIALIZER_NONE = 0;
    const SERIALIZER_PHP = 1;
    const SERIALIZER_IGBINARY = 2;
    const ATOMIC = 0;
    const MULTI = 1;
    const PIPELINE = 2;
    const REDIS_NOT_FOUND = 0;
    const REDIS_STRING = 1;
    const REDIS_SET = 2;
    const REDIS_LIST = 3;
    const REDIS_ZSET = 4;
    const REDIS_HASH = 5;
    const SCAN_NORETRY = 0;
    const SCAN_RETRY = 1;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Redis clients list. Every client build based on provided list of servers and options, component
     * can have multiple clients created, for example one for cache and one for database purposes.
     * Client instance will be created on demand.
     *
     * @var RedisClient[]
     */
    protected $clients = array();

    /**
     * Redis facade initialization.
     *
     * @param ConfiguratorInterface $configurator
     * @param Container             $container
     */
    public function __construct(ConfiguratorInterface $configurator, Container $container)
    {
        $this->config = $configurator->getConfig('redis');
        $this->container = $container;
    }

    /**
     * Get active and connected redis client. Every client build based on provided list of servers
     * and options, component can have multiple clients created, for example one for cache and one
     * for database purposes. Client instance will be created on demand.
     *
     * @param string $client Client ID.
     * @param array  $config Client options, required only for new connections (not defined in config).
     * @return RedisClient
     * @throws RedisException
     */
    public function client($client = 'default', array $config = array())
    {
        if (isset($this->config['aliases'][$client]))
        {
            $client = $this->config['aliases'][$client];
        }

        if (isset($this->clients[$client]))
        {
            return $this->clients[$client];
        }

        if (empty($config))
        {
            if (!isset($this->config['clients'][$client]))
            {
                throw new RedisException(
                    "Unable to initiate redis client, no presets for '{$client}' found."
                );
            }

            $config = $this->config['clients'][$client];
        }

        //Creating client
        benchmark('redis::client', $client);

        $this->clients[$client] = $this->container->get(
            RedisClient::class,
            array(
                'parameters' => $config['servers'],
                'options'    => isset($config['options']) ? $config['options'] : array(),
            ),
            null,
            true
        );

        benchmark('redis::client', $client);

        return $this->clients[$client];
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
    public static function resolveInjection(
        \ReflectionClass $class,
        \ReflectionParameter $parameter,
        Container $container
    )
    {
        return self::getInstance($container)->client($parameter->getName());
    }

    /**
     * Perform method from default redis client.
     *
     * @param string $method    Redis client command.
     * @param array  $arguments Command arguments.
     * @return mixed
     */
    public function command($method, array $arguments = array())
    {
        return call_user_func_array(array($this->client(), $method), $arguments);
    }

    /**
     * Bypass to perform method from default redis client.
     *
     * @param string $method    Redis client method name.
     * @param array  $arguments Redis client
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return $this->command($method, $arguments);
    }
}