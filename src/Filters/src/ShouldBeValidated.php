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
     * Get validation rules.
     */
    public function validationRules(): array;
}
