<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
use Spiral\Core\Core;
use Spiral\Core\DirectoriesInterface;
use Spiral\Debug\Dumper;
use Spiral\Translator\Exceptions\TranslatorException;
use Spiral\Translator\TranslatorInterface;

if (!function_exists('spiral')) {
    /**
     * Shortcut to shared container get method.
     *
     * @param string $alias Class name or alias.
     * @return object|null
     * @throws \Interop\Container\Exception\ContainerException
     */
    function spiral($alias)
    {
        return Core::sharedContainer()->get($alias);
    }
}

if (!function_exists('directory')) {
    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @return string
     */
    function directory($alias)
    {
        return spiral(DirectoriesInterface::class)->directory($alias);
    }
}

if (!function_exists('e')) {

    /**
     * Short alias for htmlentities(). This function is identical to htmlspecialchars() in all ways,
     * except with htmlentities(), all characters which have HTML character entity equivalents are
     * translated into these entities.
     *
     * @param string $string
     * @return string
     */
    function e($string)
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('interpolate')) {

    /**
     * Format string using previously named arguments from values array. Arguments that are not found
     * will be skipped without any notification. Extra arguments will be skipped as well.
     *
     * Example:
     * Hello [:name]! Good [:time]!
     * + array('name'=>'Member','time'=>'day')
     *
     * Output:
     * Hello Member! Good Day!
     *
     * @param string $format  Formatted string.
     * @param array  $values  Arguments (key=>value). Will skip n
     * @param string $prefix  Value prefix, "{" by default.
     * @param string $postfix Value postfix "}" by default.
     * @return mixed
     */
    function interpolate($format, array $values, $prefix = '{', $postfix = '}')
    {
        return \Spiral\interpolate($format, $values, $prefix, $postfix);
    }
}

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

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @todo make it work thought EnvironmentInterface and shared container
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {

            if (!array_key_exists($value, $_ENV)) {
                return $default;
            }

            $value = $_ENV[$value];
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;

            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}