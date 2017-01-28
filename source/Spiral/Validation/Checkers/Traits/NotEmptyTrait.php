<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Validation\Checkers\Traits;

/**
 * Default not empty check, shared across checkers.
 */
trait NotEmptyTrait
{
    /**
     * Value should not be empty.
     *
     * @param mixed $value
     * @param bool  $asString Cut spaces and make sure it's not empty when value is string.
     *
     * @return bool
     */
    public function notEmpty($value, bool $asString = true): bool
    {
        if ($asString && is_string($value) && strlen(trim($value)) == 0) {
            return false;
        }

        return !empty($value);
    }
}