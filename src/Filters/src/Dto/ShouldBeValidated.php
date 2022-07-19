<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto;

interface ShouldBeValidated
{
    /**
     * Get validation rules.
     */
    public function validationRules(): array;
}
