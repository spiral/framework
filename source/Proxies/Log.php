<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Spiral\Components\Debug\Debugger;

/**
 * @method static emergency($message, array $context = [])
 * @method static alert($message, array $context = [])
 * @method static critical($message, array $context = [])
 * @method static error($message, array $context = [])
 * @method static warning($message, array $context = [])
 * @method static notice($message, array $context = [])
 * @method static info($message, array $context = [])
 * @method static debug($message, array $context = [])
 * @method static log($level, $message, array $context = [])
 */
class Log
{
    /**
     * Forwarding call to default debug logger.
     *
     * @param string $method    Method to be called.
     * @param array  $arguments Method arguments.
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([Debugger::logger(), $method], $arguments);
    }
}