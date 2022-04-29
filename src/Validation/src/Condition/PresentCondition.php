<?php

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

/**
 * Passes when all of the fields are explicitly provided in the request.
 */
final class PresentCondition extends AbstractCondition
{
    public function isMet(ValidatorInterface $validator, string $field, mixed $value): bool
    {
        foreach ($this->options as $option) {
            if (!$validator->hasValue($option)) {
                return false;
            }
        }

        return true;
    }
}
