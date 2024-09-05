<?php

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
     * @template T
     * @param class-string<T>|string $alias Class name or alias.
     * @return T
     * @psalm-return ($alias is class-string ? T : mixed)
     *
     * @throws ScopeException
     */
    function spiral(string $alias): mixed
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
     * @param non-empty-string $alias Directory alias, ie. "framework".
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
     * @param non-empty-string $key
     * @return mixed
     */
    function env(string $key, mixed $default = null)
    {
        return spiral(EnvironmentInterface::class)->get($key, $default);
    }
}
