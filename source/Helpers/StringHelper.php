<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class StringHelper
{
    /**
     * This describes character exceptions and aliases for StringHelper::url function. More
     * characters will be added based on user's request.
     *
     * @var array
     */
    public static $replaces = [
        'exceptions' => [
            '&amp;' => 'and'
        ],
        'russian'    => [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sht', 'ъ' => '',
            'ь' => '', 'ю' => 'yu', 'я' => 'ya', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
            'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y',
            'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sht', 'Ъ' => '', 'Ь' => '', 'Ю' => 'Yu', 'Я' => 'Ya'
        ],
        'german'     => [
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'b'
        ],
        'spanish'    => [
            'Ñ' => 'N'
        ]
    ];

    /**
     * Create a random string with desired length.
     *
     * @param int $length String length. 32 symbols by default.
     * @return string
     */
    public static function random($length = 32)
    {
        $string = openssl_random_pseudo_bytes($length);

        if (empty($string))
        {
            throw new \RuntimeException("Unable to generate random string.");
        }

        return substr(base64_encode($string), 0, $length);
    }

    /**
     * Applies htmlentities() and strip_tags() to string (if enabled). Can be used to clean up
     * data before rendering it in HTML.
     *
     * @param string $string    String to be escaped.
     * @param bool   $stripTags Will remove all tags using strip_tags(). Disabled by default.
     * @return string
     */
    public static function escape($string, $stripTags = false)
    {
        if (is_array($string) || is_object($string))
        {
            return '';
        }

        if ($stripTags)
        {
            $string = strip_tags($string);
        }

        return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Convert string to URL supported identifier. Will erase any bad symbols, beginning and ending
     * characters and double delimiters. This function will use StringHelper::$replaces array to
     * support non English strings valid for URLs.
     *
     * @param string $string    String that will be converted.
     * @param string $delimiter Segments delimiter, "-" by default.
     * @return string
     */
    public static function urlSlug($string, $delimiter = '-')
    {
        foreach (self::$replaces as $exceptions)
        {
            $string = str_replace(array_keys($exceptions), array_values($exceptions), $string);
        }

        $string = preg_replace('/([^a-z0-9_-]|' . $delimiter . ')+/iu', $delimiter, $string);

        //End and start characters
        return trim($string, $delimiter);
    }

    /**
     * Shorter string with specified limit. UTF8 encoding will be used to support non English strings.
     *
     * @param string $string
     * @param int    $limit The max string length, 300 by default.
     * @return string
     */
    public static function shorter($string, $limit = 300)
    {
        if (mb_strlen($string) + 3 > $limit)
        {
            return trim(mb_substr($string, 0, $limit - 3, 'UTF-8')) . '...';
        }

        return $string;
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes    Size in bytes.
     * @param int $decimals The number of decimals include to output. Set to 1 by default.
     * @return string
     */
    public static function formatBytes($bytes, $decimals = 1)
    {
        $pows = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($unit = 0; $bytes > 1024; $unit++)
        {
            $bytes /= 1024;
        }

        return number_format($bytes, $unit ? $decimals : 0) . " " . $pows[$unit];
    }

    /**
     * Normalize string endings to avoid EOL problem. Replace \n\r and multiply new lines with
     * single \n.
     *
     * @param string $string       String to be normalized.
     * @param bool   $joinMultiple Join multiple new lines into one.
     * @return mixed
     */
    public static function normalizeEndings($string, $joinMultiple = true)
    {
        if (!$joinMultiple)
        {
            return str_replace("\r\n", "\n", $string);
        }

        return preg_replace('/[\n\r]+/', "\n", $string);
    }

    /**
     * Shift all string lines to have minimum indent size set to 0.
     *
     * Example:
     * |-a
     * |--b
     * |--c
     * |---d
     *
     * Output:
     * |a
     * |-b
     * |-c
     * |--d
     *
     * @param string $string         Input string with multiple lines.
     * @param string $tabulationCost How to treat \t symbols relatively to spaces. By default, this
     *                               is set to 4 spaces.
     * @return string
     */
    public static function normalizeIndents($string, $tabulationCost = "   ")
    {
        $string = self::normalizeEndings($string, false);
        $lines = explode("\n", $string);

        $minIndent = null;
        foreach ($lines as $line)
        {
            if (!trim($line))
            {
                continue;
            }

            $line = str_replace("\t", $tabulationCost, $line);

            //Getting indent size
            if (!preg_match("/^( +)/", $line, $matches))
            {
                //Some line has no indent
                return $string;
            }

            if ($minIndent === null)
            {
                $minIndent = strlen($matches[1]);
            }

            $minIndent = min($minIndent, strlen($matches[1]));
        }

        if (is_null($minIndent) || $minIndent === 0)
        {
            return $string;
        }

        //Fixing indent
        foreach ($lines as &$line)
        {
            if (empty($line))
            {
                continue;
            }

            //Getting line indent
            preg_match("/^([ \t]+)/", $line, $matches);
            $indent = $matches[1];

            if (!trim($line))
            {
                $line = '';
                continue;
            }

            //Getting new indent
            $useIndent = str_repeat(
                " ",
                strlen(str_replace("\t", $tabulationCost, $indent)) - $minIndent
            );

            $line = $useIndent . substr($line, strlen($indent));
            unset($line);
        }

        return join("\n", $lines);
    }

    /**
     * Format string using previously named arguments from values array. Arguments that are not
     * found will be skipped without any notification. Extra arguments will be skipped as well.
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
    public static function interpolate($format, array $values, $prefix = '{', $postfix = '}')
    {
        if (empty($values))
        {
            return $format;
        }

        $replace = [];
        foreach ($values as $key => $value)
        {
            $value = (is_array($value) || $value instanceof \Closure) ? '' : $value;

            try
            {
                if (is_object($value))
                {
                    $value = (string)$value;
                }
            }
            catch (\Exception $e)
            {
                $value = '';
            }

            $replace[$prefix . $key . $postfix] = $value;
        }

        return strtr($format, $replace);
    }
}