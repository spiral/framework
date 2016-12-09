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
use Spiral\Http\Routing\RouterInterface;
use Spiral\Translator\Exceptions\TranslatorException;
use Spiral\Translator\TranslatorInterface;


if (!function_exists('dump')) {
    /**
     * Dump value.
     *
     * @param mixed $value Value to be dumped.
     * @param int   $output
     * @return null|string
     */
    function dump($value, $output = Dumper::OUTPUT_ECHO)
    {
        return spiral(Dumper::class)->dump($value, $output);
    }
}

if (!function_exists('l')) {
    /**
     * Translate message using default or specific bundle name.
     *
     * Examples:
     * l('Some Message');
     * l('Hello {name}!', ['name' => $name]);
     *
     * @param string $string
     * @param array  $options
     * @param string $domain
     * @return string
     * @throws TranslatorException
     */
    function l($string, array $options = [], $domain = TranslatorInterface::DEFAULT_DOMAIN)
    {
        return spiral(TranslatorInterface::class)->trans($string, $options, $domain);
    }
}

if (!function_exists('p')) {
    /**
     * Pluralize string using language pluralization options and specified numeric value.
     *
     * Examples:
     * p("{n} user|{n} users", $users);
     *
     * @param string $string Can include {n} as placeholder.
     * @param int    $number
     * @param array  $options
     * @param string $domain
     * @return string
     * @throws TranslatorException
     */
    function p(
        $string,
        $number,
        array $options = [],
        $domain = TranslatorInterface::DEFAULT_DOMAIN
    ) {
        return spiral(TranslatorInterface::class)->transChoice($string, $number, $options, $domain);
    }
}

if (!function_exists('uri')) {
    /**
     * Create uri for route and parameters.
     *
     * @param string $route
     * @param array  $parameters
     * @return \Psr\Http\Message\UriInterface
     */
    function uri($route, $parameters = [])
    {
        if (!is_array($parameters) && !$parameters instanceof Traversable) {
            $parameters = array_slice(func_get_args(), 1);
        }

        return spiral(RouterInterface::class)->uri($route, $parameters);
    }
}
