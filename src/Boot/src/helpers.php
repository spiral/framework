<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\DirectoryException;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;

if (!function_exists('spiral')) {
    /**
     * Resolve given alias in current IoC scope.
     *
     * @param string $alias Class name or alias.
     * @return object|null
     *
     * @throws ScopeException
     */
    function spiral(string $alias)
    {
        if (ContainerScope::getContainer() === null) {
            throw new ScopeException('Container scope was not set.');
        }

        try {
            return ContainerScope::getContainer()->get($alias);
        } catch (ContainerExceptionInterface $e) {
            throw new ScopeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

if (!function_exists('directory')) {
    /**
     * Get directory alias value. Uses application core from the current global scope.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @return string
     *
     * @throws ScopeException
     * @throws DirectoryException
     */
    function directory(string $alias): string
    {
        return spiral(DirectoriesInterface::class)->get($alias);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Uses application core from the current global scope.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return spiral(EnvironmentInterface::class)->get($key, $default);
    }
}
