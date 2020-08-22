<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation\Checker\Traits;

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
     * @return bool
     */
    public function notEmpty($value, bool $asString = true): bool
    {
        if ($asString && is_string($value) && trim($value) === '') {
            return false;
        }

        return !empty($value);
    }
}
