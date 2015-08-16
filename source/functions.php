<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
use Spiral\Core\Core;
use Spiral\Debug\Dumper;
use Spiral\Translator\Exceptions\TranslatorException;
use Spiral\Translator\TranslatorInterface;

if (!function_exists('directory')) {

    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @return string
     */
    function directory($alias)
    {
        return Core::instance()->directory($alias);
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
     * Helper function to dump variable into specified destination (output, log or return) using
     * pre-defined dumping styles. This method is fairly slow and should not be used in productions
     * environment. Only use it during development, error handling and other not high loaded
     * application parts. Method is an alias for Debug::dump() method.
     *
     * @param mixed $value      Value to be dumped.
     * @param int   $output     Output method, can print, return or log value dump.
     * @param bool  $showStatic Set true to dump all static object properties.
     * @return null|string
     */
    function dump($value, $output = Dumper::OUTPUT_ECHO, $showStatic = false)
    {
        return Core::container()->get(Dumper::class)->dump($value, $output, $showStatic);
    }
}

if (!function_exists('l')) {

    /**
     * Translate value using active language. Method must support message interpolation using
     * interpolate method or sptrinf.
     *
     * Examples:
     * l('Some Message');
     * l('Hello %s', $name);
     *
     * @param string $string
     * @return string
     * @throws TranslatorException
     */
    function l($string)
    {
        $arguments = func_get_args();
        array_unshift($arguments, TranslatorInterface::DEFAULT_BUNDLE);

        return call_user_func_array(
            [Core::container()->get(TranslatorInterface::class), 'translate'], $arguments
        );
    }
}

if (!function_exists('p')) {

    /**
     * Pluralize string using language pluralization options and specified numeric value. Number
     * has to be ingested at place of {n} placeholder.
     *
     * Examples:
     * p("{n} user", $users);
     *
     * @param string $phrase Should include {n} as placeholder.
     * @param int    $number
     * @param bool   $format Format number.
     * @return string
     * @throws TranslatorException
     */
    function p($phrase, $number, $format = true)
    {
        return Core::container()->get(TranslatorInterface::class)->pluralize(
            $phrase, $number, $format
        );
    }
}