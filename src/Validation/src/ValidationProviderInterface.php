<?php

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * @template TValidation
 * @template TFilterDefinition
 */
interface ValidationProviderInterface
{
    /**
     * Get validation object by name.
     * @param class-string<TFilterDefinition> $name
     * @psalm-return TValidation
     */
    public function getValidation(string $name, array $params = []): ValidationInterface;
}
