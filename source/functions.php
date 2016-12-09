<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
use Spiral\Core\Core;
use Spiral\Core\DirectoriesInterface;
use Spiral\Core\EnvironmentInterface;
use Spiral\Debug\Dumper;

if (!function_exists('spiral')) {
    /**
     * Shortcut to shared container get method.
     *
     * @param string $alias Class name or alias.
     *
     * @return object|null
     * @throws \Interop\Container\Exception\ContainerException
     */
    function spiral(string $alias)
    {
        return Core::sharedContainer()->get($alias);
    }
}

if (!function_exists('directory')) {
    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     *
     * @return string
     */
    function directory(string $alias): string
    {
        return spiral(DirectoriesInterface::class)->directory($alias);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
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
     * @param string $string
     *
     * @return string
     */
    function e(string $string): string
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump value.
     *
     * @param mixed $value Value to be dumped.
     * @param int   $output
     *
     * @return string
     */
    function dump($value, $output = Dumper::OUTPUT_ECHO): string
    {
        return spiral(Dumper::class)->dump($value, $output);
    }
}

//todo: interpolate