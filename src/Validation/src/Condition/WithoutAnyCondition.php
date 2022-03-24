<?php

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

/**
 * Fires when any of required values are missing.
 */
final class WithoutAnyCondition extends AbstractCondition
{
    public function isMet(ValidatorInterface $validator, string $field, mixed $value): bool
    {
        foreach ($this->options as $option) {
            if (empty($validator->getValue($option))) {
                return true;
            }
        }

        return false;
    }
}
