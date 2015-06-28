<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Debug\Debugger;

/**
 * @method static emergency($message, array $context = array())
 * @method static alert($message, array $context = array())
 * @method static critical($message, array $context = array())
 * @method static error($message, array $context = array())
 * @method static warning($message, array $context = array())
 * @method static notice($message, array $context = array())
 * @method static info($message, array $context = array())
 * @method static debug($message, array $context = array())
 * @method static log($level, $message, array $context = array())
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
        return call_user_func_array(array(Debugger::logger(), $method), $arguments);
    }
}