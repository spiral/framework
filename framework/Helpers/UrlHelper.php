<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class UrlHelper
{
    /**
     * Normalize URL to always include http or https protocols. Other protocols are not supported and url will be corrupted
     * which will cause it to fail upon validation. Empty URL will be returned as an empty strings.
     *
     * @param string $URL URL to be normalized.
     * @return string
     */
    public static function normalizeURL($URL)
    {
        if (!$URL)
        {
            return '';
        }

        if (stripos($URL, 'http://') === false && stripos($URL, 'https://') === false)
        {
            $URL = 'http://' . $URL;
        }

        return $URL;
    }

    /**
     * Convert string to a URL supported identifier. Will erase every bad symbol, beginning and end characters and double
     * delimiters. This function will use StringHelper::$replaces array to support non English strings still valid for URLs.
     * Alias for StringHelper::url.
     *
     * @param string $string    String to be converted.
     * @param string $delimiter Segments delimiter, "-" by default.
     * @return string
     */
    public static function convert($string, $delimiter = '-')
    {
        return StringHelper::url($string, $delimiter);
    }
}