<?php

declare(strict_types=1);

namespace Spiral\Validation;

interface ValidationProviderInterface
{
    /**
     * Get validation object by name.
     *
     * @param non-empty-string $name
     */
    public function getValidation(string $name, array $params = []): ValidationInterface;
}
