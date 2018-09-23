<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

if (!function_exists('bind')) {
    /**
     * Shortcut to container Autowire definition.
     *
     * Example:
     * 'name' => bind(SomeClass::name, [...])
     *
     * @param string $alias Class name or alias.
     * @param array  $parameters
     *
     * @return \Spiral\Core\Container\Autowire
     */
    function bind(string $alias, array $parameters = [])
    {
        return new \Spiral\Core\Container\Autowire($alias, $parameters);
    }
}