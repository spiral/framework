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
use Spiral\ODM\ODM;

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

if (!function_exists('mongoID'))
{
    /**
     * Create valid MongoId object based on string or id provided from client side, this function
     * can be used as model filter as it will pass MongoId objects without any change.
     *
     * @param mixed $mongoID String or MongoId object.
     * @return \MongoId|null
     */
    function mongoID($mongoID)
    {
        return ODM::mongoID($mongoID);
    }
}