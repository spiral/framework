<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class ArrayHelper
{
    /**
     * Fetches the desired array keys. Usually used while filtering requests or for non limited data
     * sets.
     *
     * @param array $haystack Source array.
     * @param array $keys     Desired keys to fetch.
     * @return array
     */
    public static function fetchKeys(array $haystack, array $keys)
    {
        return array_intersect_key($haystack, array_flip($keys));
    }

    /**
     * This will remove the element from haystack array by it's value. Value is searched using an
     * array_search method which can cause behaviors described in function documentation.
     *
     * @link http://php.net/manual/en/function.array-search.php
     * @param array $haystack Source array.
     * @param mixed $needle   Element to remove. Non strict comparison is used.
     * @return array
     */
    public static function removeElement(array $haystack, $needle)
    {
        $key = array_search($needle, $haystack);
        if ($key !== false)
        {
            unset($haystack[$key]);
        }

        return $haystack;
    }

    /**
     * Will check that all haystack values are present in the allowed array. If one is not present,
     * this will return false.
     *
     * @param array $haystack Source array.
     * @param array $allowed  Filtering array.
     * @return bool
     */
    public static function checkValues(array $haystack, array $allowed)
    {
        if (!is_array($haystack))
        {
            return false;
        }

        foreach ($haystack as $value)
        {
            if (!in_array($value, $allowed))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Sort an array with a user-defined comparison function and maintain index association. This is
     * stable sort which will keep order or elements if their compare result is equal.
     *
     * @link http://stackoverflow.com/questions/1517793/stability-in-sorting-algorithms
     * @link http://php.net/manual/en/function.uasort.php
     * @param array    $array    The input array.
     * @param callable $function User-defined comparison functions.
     */
    public static function stableSort(&$array, $function)
    {
        if (count($array) < 2)
        {
            return;
        }
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway, true);
        $array2 = array_slice($array, $halfway, null, true);

        self::stableSort($array1, $function);
        self::stableSort($array2, $function);
        if (call_user_func($function, end($array1), reset($array2)) < 1)
        {
            $array = $array1 + $array2;

            return;
        }
        $array = array();

        reset($array1);
        reset($array2);

        while (current($array1) && current($array2))
        {
            if (call_user_func($function, current($array1), current($array2)) < 1)
            {
                $array[key($array1)] = current($array1);
                next($array1);
            }
            else
            {
                $array[key($array2)] = current($array2);
                next($array2);
            }
        }
        while (current($array1))
        {
            $array[key($array1)] = current($array1);
            next($array1);
        }
        while (current($array2))
        {
            $array[key($array2)] = current($array2);
            next($array2);
        }

        return;
    }
}