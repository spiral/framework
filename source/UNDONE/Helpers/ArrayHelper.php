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
    public static function fetch(array $haystack, array $keys)
    {
        return array_intersect_key($haystack, array_flip($keys));
    }
}