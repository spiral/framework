<?php

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * Enables and disabled field validation.
 */
interface ConditionInterface
{
    /**
     * Checks if condition is met and field must be validated by the following rule.
     */
    public function isMet(ValidatorInterface $validator, string $field, mixed $value): bool;

    public function withOptions(?array $options): ConditionInterface;
}
