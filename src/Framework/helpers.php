<?php

declare(strict_types=1);

use Spiral\Core\Container\Autowire;

if (!\function_exists('bind')) {
    /**
     * Shortcut to container Autowire definition.
     *
     * Example:
     * 'name' => bind(SomeClass::name, [...])
     *
     * @param string $alias Class name or alias.
     */
    function bind(string $alias, array $parameters = []): Autowire
    {
        return new Autowire($alias, $parameters);
    }
}
