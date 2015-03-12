<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class ValueHelper
{
    /**
     * Will convert the input variable in any format into a string. This function bypasses the
     * problem of applying strval() to arrays.
     *
     * @param mixed $variable Input variable. Any format is allowed.
     * @return string
     */
    public static function castString($variable)
    {
        if (is_array($variable))
        {
            return '';
        }

        if (is_object($variable))
        {
            if (method_exists($variable, '__toString'))
            {
                return $variable->__toString();
            }

            return '';
        }

        return strval($variable);
    }

    /**
     * Will filter and return only the scalar values of input haystack. Sub arrays are counted as
     * non scalar values and will be removed.
     *
     * @param mixed $haystack Filtered array
     * @return array
     */
    public static function scalarArray(array $haystack)
    {
        if (!is_array($haystack))
        {
            return array();
        }

        return array_filter(array_values($haystack), 'is_scalar');
    }

    /**
     * Will return a real boolean representation for variable (important for MongoDB). This is the
     * alias for boolval() function. However it's only available in PHP 5.5.
     *
     * @param mixed $variable
     * @return bool
     */
    public static function castBoolean($variable)
    {
        if (function_exists('boolval'))
        {
            return boolval($variable);
        }

        return (bool)$variable;
    }
}