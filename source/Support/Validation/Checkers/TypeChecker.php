<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Support\Models\Accessors\Timestamp;
use Spiral\Support\Validation\Checker;

class TypeChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = [
        "notEmpty" => "[[Field '{field}' should not be empty.]]",
        "boolean"  => "[[Field '{field}' is not valid boolean.]]",
        "datetime" => "[[Field '{field}' is not valid datetime.]]",
        "timezone" => "[[Field '{field}' is not valid timezone.]]"
    ];

    /**
     * Will return true if value not empty. Uses !empty() function.
     *
     * @param mixed $value Value to be validated.
     * @return bool
     */
    public function notEmpty($value)
    {
        return !empty($value);
    }

    /**
     * Will ensure that value is true boolean, meaning it's either boolean type, or int in a range
     * or 0-1.
     *
     * @param mixed $value Value to be validated.
     * @return bool
     */
    public function boolean($value)
    {
        return is_bool($value) || (is_numeric($value) && ($value === 0 || $value === 1));
    }

    /**
     * Checks is provided string valid datetime or timestamp. TimeHelper::getTimestamp will be used
     * to process value.
     *
     * @param mixed $value Datetime string or timestamp to be validated.
     * @return bool
     */
    public function datetime($value)
    {
        return is_scalar($value) && (Timestamp::castTimestamp($value) != 0);
    }

    /**
     * Checks is provided string valid timezone. Validation rule to use while validating user presets.
     *
     * @param mixed $value Valid timezone string to be validated.
     * @return bool
     */
    public function timezone($value)
    {
        return in_array($value, \DateTimeZone::listIdentifiers());
    }
}