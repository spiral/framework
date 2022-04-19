<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\FilterException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidatorInterface;

interface ShouldBeValidated
{
    /**
     * Set the validator instance.
     */
    public function withValidation(
        ValidationInterface $validation
    ): static;

    /**
     * Get validation rules.
     */
    public function validationRules(): array;

    /**
     * Check if context data valid accordingly to provided rules.
     *
     * @throws FilterException
     * @throws ValidationException
     */
    public function validate(): ValidatorInterface;
}
