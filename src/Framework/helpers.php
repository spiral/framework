<?php

declare(strict_types=1);

use Psr\Http\Message\UriInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Router\RouterInterface;

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

if (!\function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * Example:
     * (string) route('home', ['controller' => 'HomeController'])
     */
    function route(string $name, array $parameters = []): UriInterface
    {
        return spiral(RouterInterface::class)->uri($name, $parameters);
    }
}
