<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Helpers;

/**
 * Set of helper methods to work with variable values and cross type conversions.
 */
class ValueHelper
{
    /**
     * Convert the input variable in any format into a string. This function bypasses the problem of
     * applying strval() to arrays.
     *
     * @param mixed $variable Input variable. Any format is allowed.
     * @return string
     */
    public static function castString($variable)
    {
        if (is_array($variable)) {
            return '';
        }

        if (is_object($variable)) {
            if (method_exists($variable, '__toString')) {
                return $variable->__toString();
            }

            return '';
        }

        return strval($variable);
    }

    /**
     * Filter and return only the scalar values of input haystack. Sub arrays are counted as
     * non scalar values and will be removed.
     *
     * @param mixed $haystack Filtered array
     * @return array
     */
    public static function scalarArray($haystack)
    {
        if (!is_array($haystack)) {
            return [];
        }

        return array_values(array_filter($haystack, 'is_scalar'));
    }
}