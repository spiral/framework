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
use Spiral\Components\Redis\RedisClient;
use Spiral\Components\Redis\RedisManager;
use Spiral\Core\Facade;

/**
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static RedisClient client(string $client = 'default', string $class = null)
 * @method static mixed command(string $method, array $arguments = [])
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface logger()
 * @method static RedisManager make(array $parameters = [])
 * @method static RedisManager getInstance()
 *
 * @method static mixed del(array $keys)
 * @method static mixed dump($key)
 * @method static mixed exists($key)
 * @method static mixed expire($key, $seconds)
 * @method static mixed expireat($key, $timestamp)
 * @method static mixed keys($pattern)
 * @method static mixed move($key, $db)
 * @method static mixed object($subcommand, $key)
 * @method static mixed persist($key)
 * @method static mixed pexpire($key, $milliseconds)
 * @method static mixed pexpireat($key, $timestamp)
 * @method static mixed pttl($key)
 * @method static mixed randomkey()
 * @method static mixed rename($key, $target)
 * @method static mixed renamenx($key, $target)
 * @method static mixed scan($cursor, array $options = null)
 * @method static mixed sort($key, array $options = null)
 * @method static mixed ttl($key)
 * @method static mixed type($key)
 * @method static mixed append($key, $value)
 * @method static mixed bitcount($key, $start = null, $end = null)
 * @method static mixed bitop($operation, $destkey, $key)
 * @method static mixed decr($key)
 * @method static mixed decrby($key, $decrement)
 * @method static mixed get($key)
 * @method static mixed getbit($key, $offset)
 * @method static mixed getrange($key, $start, $end)
 * @method static mixed getset($key, $value)
 * @method static mixed incr($key)
 * @method static mixed incrby($key, $increment)
 * @method static mixed incrbyfloat($key, $increment)
 * @method static mixed mget(array $keys)
 * @method static mixed mset(array $dictionary)
 * @method static mixed msetnx(array $dictionary)
 * @method static mixed psetex($key, $milliseconds, $value)
 * @method static mixed set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
 * @method static mixed setbit($key, $offset, $value)
 * @method static mixed setex($key, $seconds, $value)
 * @method static mixed setnx($key, $value)
 * @method static mixed setrange($key, $offset, $value)
 * @method static mixed strlen($key)
 * @method static mixed hdel($key, array $fields)
 * @method static mixed hexists($key, $field)
 * @method static mixed hget($key, $field)
 * @method static mixed hgetall($key)
 * @method static mixed hincrby($key, $field, $increment)
 * @method static mixed hincrbyfloat($key, $field, $increment)
 * @method static mixed hkeys($key)
 * @method static mixed hlen($key)
 * @method static mixed hmget($key, array $fields)
 * @method static mixed hmset($key, array $dictionary)
 * @method static mixed hscan($key, $cursor, array $options = null)
 * @method static mixed hset($key, $field, $value)
 * @method static mixed hsetnx($key, $field, $value)
 * @method static mixed hvals($key)
 * @method static mixed blpop(array $keys, $timeout)
 * @method static mixed brpop(array $keys, $timeout)
 * @method static mixed brpoplpush($source, $destination, $timeout)
 * @method static mixed lindex($key, $index)
 * @method static mixed linsert($key, $whence, $pivot, $value)
 * @method static mixed llen($key)
 * @method static mixed lpop($key)
 * @method static mixed lpush($key, array $values)
 * @method static mixed lpushx($key, $value)
 * @method static mixed lrange($key, $start, $stop)
 * @method static mixed lrem($key, $count, $value)
 * @method static mixed lset($key, $index, $value)
 * @method static mixed ltrim($key, $start, $stop)
 * @method static mixed rpop($key)
 * @method static mixed rpoplpush($source, $destination)
 * @method static mixed rpush($key, array $values)
 * @method static mixed rpushx($key, $value)
 * @method static mixed sadd($key, array $members)
 * @method static mixed scard($key)
 * @method static mixed sdiff(array $keys)
 * @method static mixed sdiffstore($destination, array $keys)
 * @method static mixed sinter(array $keys)
 * @method static mixed sinterstore($destination, array $keys)
 * @method static mixed sismember($key, $member)
 * @method static mixed smembers($key)
 * @method static mixed smove($source, $destination, $member)
 * @method static mixed spop($key)
 * @method static mixed srandmember($key, $count = null)
 * @method static mixed srem($key, $member)
 * @method static mixed sscan($key, $cursor, array $options = null)
 * @method static mixed sunion(array $keys)
 * @method static mixed sunionstore($destination, array $keys)
 * @method static mixed zadd($key, array $membersAndScoresDictionary)
 * @method static mixed zcard($key)
 * @method static mixed zcount($key, $min, $max)
 * @method static mixed zincrby($key, $increment, $member)
 * @method static mixed zinterstore($destination, array $keys, array $options = null)
 * @method static mixed zrange($key, $start, $stop, array $options = null)
 * @method static mixed zrangebyscore($key, $min, $max, array $options = null)
 * @method static mixed zrank($key, $member)
 * @method static mixed zrem($key, $member)
 * @method static mixed zremrangebyrank($key, $start, $stop)
 * @method static mixed zremrangebyscore($key, $min, $max)
 * @method static mixed zrevrange($key, $start, $stop, array $options = null)
 * @method static mixed zrevrangebyscore($key, $min, $max, array $options = null)
 * @method static mixed zrevrank($key, $member)
 * @method static mixed zunionstore($destination, array $keys, array $options = null)
 * @method static mixed zscore($key, $member)
 * @method static mixed zscan($key, $cursor, array $options = null)
 * @method static mixed zrangebylex($key, $start, $stop, array $options = null)
 * @method static mixed zremrangebylex($key, $min, $max)
 * @method static mixed zlexcount($key, $min, $max)
 * @method static mixed pfadd($key, array $elements)
 * @method static mixed pfmerge($destinationKey, array $sourceKeys)
 * @method static mixed pfcount(array $keys)
 * @method static mixed pubsub($subcommand, $argument)
 * @method static mixed publish($channel, $message)
 * @method static mixed discard()
 * @method static mixed exec()
 * @method static mixed multi()
 * @method static mixed unwatch()
 * @method static mixed watch($key)
 * @method static mixed eval($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
 * @method static mixed evalsha($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
 * @method static mixed script($subcommand, $argument = null)
 * @method static mixed auth($password)
 * @method static mixed echo ($message)
 * @method static mixed ping($message = null)
 * @method static mixed select($database)
 * @method static mixed bgrewriteaof()
 * @method static mixed bgsave()
 * @method static mixed config($subcommand, $argument = null)
 * @method static mixed dbsize()
 * @method static mixed flushall()
 * @method static mixed flushdb()
 * @method static mixed info($section = null)
 * @method static mixed lastsave()
 * @method static mixed save()
 * @method static mixed slaveof($host, $port)
 * @method static mixed slowlog($subcommand, $argument = null)
 * @method static mixed time()
 */
class Redis extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'redis';

    /**
     * Copying set of redis constants due same name were used.
     */
    const AFTER               = 'after';
    const BEFORE              = 'before';
    const OPT_SERIALIZER      = 1;
    const OPT_PREFIX          = 2;
    const OPT_READ_TIMEOUT    = 3;
    const OPT_SCAN            = 4;
    const SERIALIZER_NONE     = 0;
    const SERIALIZER_PHP      = 1;
    const SERIALIZER_IGBINARY = 2;
    const ATOMIC              = 0;
    const MULTI               = 1;
    const PIPELINE            = 2;
    const REDIS_NOT_FOUND     = 0;
    const REDIS_STRING        = 1;
    const REDIS_SET           = 2;
    const REDIS_LIST          = 3;
    const REDIS_ZSET          = 4;
    const REDIS_HASH          = 5;
    const SCAN_NORETRY        = 0;
    const SCAN_RETRY          = 1;
}