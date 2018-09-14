<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Framework\DirectoriesInterface;
use Spiral\Framework\EnvironmentInterface;
use Spiral\Framework\Exceptions\DirectoryException;

if (!function_exists('bind')) {
    /**
     * Shortcut for new Autowire().
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
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     *
     * @return string
     *
     * @throws ScopeException
     * @throws DirectoryException
     */
    function directory(string $alias): string
    {
        return spiral(DirectoriesInterface::class)->directory($alias);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return spiral(EnvironmentInterface::class)->get($key, $default);
    }
}

if (!function_exists('e')) {
    /**
     * Short alias for htmlentities(). This function is identical to htmlspecialchars() in all ways,
     * except with htmlentities(), all characters which have HTML character entity equivalents are
     * translated into these entities.
     *
     * @param string|null $string
     *
     * @return string
     */
    function e(string $string = null): string
    {
        if (is_null($string)) {
            return '';
        }

        return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
    }
}