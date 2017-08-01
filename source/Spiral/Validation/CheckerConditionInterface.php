<?php

namespace Spiral\Validation;

/**
 * Provides ability to execute checker on a certain condition if it is met.
 */
interface CheckerConditionInterface
{
    /**
     * @param ValidatorInterface $validator
     *
     * @return CheckerConditionInterface
     */
    public function withValidator(ValidatorInterface $validator): CheckerConditionInterface;

    /**
     * @return bool
     */
    public function isMet(): bool;
}