<?php

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * Creates validators with given rules and data.
 */
interface ValidationInterface
{
    /**
     * Create validator for given parameters.
     *
     * @param array|object  $data    Target validation data.
     * @param array         $rules   List of associated validation rules (see Rule).
     * @param mixed         $context Validation context (available for checkers and validation
     *                               methods but is not validated).
     */
    public function validate(array|object $data, array $rules, mixed $context = null): ValidatorInterface;
}
