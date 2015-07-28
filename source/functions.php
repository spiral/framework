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
use Spiral\Translator\TranslatorInterface;

if (!function_exists('directory'))
{
    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @return null
     */
    function directory($alias)
    {
        return Core::getInstance()->directory($alias);
    }
}

if (!function_exists('e'))
{
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

if (!function_exists('interpolate'))
{
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

if (!function_exists('dump'))
{
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
    function dump($value, $output = Dumper::DUMP_ECHO, $showStatic = false)
    {
        return Dumper::getInstance()->dump($value, $output, $showStatic);
    }
}

if (!function_exists('l'))
{
    /**
     * Translate and format string fetched from bundle, new strings will be automatically registered
     * in bundle with key identical to string itself. Function support embedded formatting, to
     * enable it provide arguments to insert after string. This method is indexable and will be
     * automatically collected to bundles. This function is short alias for I18n::get() method with
     * forced default bundle id. "L" is legacy name, this function probably better be named as "t".
     *
     * Examples:
     * l('Some Message');
     * l('Hello %s', $name);
     *
     * @param string $string String to be localized, should be sprintf compatible if formatting
     *                       required.
     * @return string
     */
    function l($string)
    {
        $arguments = func_get_args();
        array_unshift($arguments, TranslatorInterface::DEFAULT_BUNDLE);

        return call_user_func_array(
            [Core::getContainer()->get(TranslatorInterface::class), 'translate'], $arguments
        );
    }
}

if (!function_exists('p'))
{
    /**
     * Format phase according to formula defined in selected language. Phase should include "%s"
     * which will be replaced with number provided as second argument. This method is indexable
     * and will be automatically collected to bundles. This function is short alias for
     * I18n::pluralize() method.
     *
     * Examples:
     * p("%s user", $users);
     *
     * All pluralization phases stored in same bundle defined in i18n config.
     *
     * @param string $phrase Pluralization phase.
     * @param int    $number
     * @param bool   $numberFormat
     * @return string
     */
    function p($phrase, $number, $numberFormat = true)
    {
        return Core::getContainer()->get(TranslatorInterface::class)->pluralize(
            $phrase, $number, $numberFormat
        );
    }
}