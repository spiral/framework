<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

use Spiral\Core\Container\Autowire;
use Spiral\Debug\Dumper;

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
     * @return Autowire
     */
    function bind(string $alias, array $parameters = []): Autowire
    {
        return new Autowire($alias, $parameters);
    }
}

if (!function_exists('dumprr')) {
    /**
     * Dumprr is similar to Dump function but always redirect output to STDERR.
     *
     * @param mixed $value
     */
    function dumprr($value): void
    {
        dump($value, Dumper::ERROR_LOG);
    }
}
