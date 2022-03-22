<?php

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
     * @param bool  $asString Cut spaces and make sure it's not empty when value is string.
     */
    public function notEmpty(mixed $value, bool $asString = true): bool
    {
        if ($asString && \is_string($value) && \trim($value) === '') {
            return false;
        }

        return !empty($value);
    }
}
