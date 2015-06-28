<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Support\Validation\Checker;

class StringChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = array(
        "regexp"  => "[[Field '{field}' does not match required pattern.]]",
        "shorter" => "[[Field length '{field}' should be shorter or equal to {0}.]]",
        "longer"  => "[[Field length '{field}' should be longer or equal to {0}.]]",
        "exactly" => "[[Field length '{field}' should be exactly equal to {0}.]]",
        "range"   => "[[Field length '{field}' should be in range of {0}-{1}.]]"
    );

    /**
     * Checks string using regular expression. Using preg_math as validation method.
     *
     * @param string $string     Value to be validated.
     * @param string $expression Valid regexp expression will be applied to rule.
     * @return bool
     */
    public function regexp($string, $expression)
    {
        return is_string($string) && preg_match($expression, $string);
    }

    /**
     * Check if provided string length is less or equal than required value. String length will be
     * retrieved using mb_strlen() function, so aware of results with multi-byte strings.
     *
     * @param string $string Value to be validated.
     * @param int    $length Maximum string length.
     * @return bool
     */
    public function shorter($string, $length)
    {
        return mb_strlen($string) <= $length;
    }

    /**
     * Check if provided string length is equal or higher than required value. String length will be
     * retrieved using mb_strlen() function, so aware of results with multi-byte strings.
     *
     * @param string $string Value to be validated.
     * @param int    $length Maximum string length.
     * @return bool
     */
    public function longer($string, $length)
    {
        return mb_strlen($string) >= $length;
    }

    /**
     * Check if provided string length is equal to required value. String length will be retrieved
     * using mb_strlen() function, so aware of results with multi-byte strings.
     *
     * @param string $string Value to be validated.
     * @param int    $length Desired string length.
     * @return bool
     */
    public function exactly($string, $length)
    {
        return mb_strlen($string) == $length;
    }

    /**
     * Check if provided string length is equal or higher than required value. String length will be
     * retrieved using mb_strlen() function, so aware of results with multi-byte strings.
     *
     * @param string $string  Value to be validated.
     * @param int    $lengthA Minimum string length.
     * @param int    $lengthB Maximum string length.
     * @return bool
     */
    public function range($string, $lengthA, $lengthB)
    {
        return (mb_strlen($string) >= $lengthA) && (mb_strlen($string) <= $lengthB);
    }
}