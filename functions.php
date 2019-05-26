<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

use Spiral\Debug\Dumper;

declare(strict_types=1);

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

if (!function_exists('dumprr')) {
    /**
     * Dumprr is similar to Dump function but always redirect output to STDERR.
     *
     * @param mixed $value
     */
    function dumprr($value)
    {
        dump($value, Dumper::ERROR_LOG);
    }
}