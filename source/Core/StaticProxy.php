<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

class StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = '';

    /**
     * Forwarding call to component instance resolved via IoC.
     *
     * @param string $method    Method to be called.
     * @param array  $arguments Method arguments.
     * @return mixed
     * @throws CoreException
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([
            Container::getInstance()->get(static::COMPONENT), $method],
            $arguments
        );
    }
}