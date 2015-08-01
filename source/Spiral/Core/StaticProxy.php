<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Console\Exceptions\ConsoleException;

/**
 * StaticProxy provides static access to one of container binding. Please do not use StaticProxies.
 */
class StaticProxy
{
    /**
     * Binded component class name or alias.
     */
    const COMPONENT = '';

    /**
     * Forward call to Container.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws ConsoleException
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array(
            [Container::container()->get(static::COMPONENT), $method],
            $arguments
        );
    }
}