<?php

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * @template TValidation
 */
interface ValidationProviderInterface
{
    /**
     * Get validation object by name.
     * @param class-string<TValidation> $name
     * @psalm-return TValidation
     */
    public function getValidation(string $name, array $params = []): ValidationInterface;
}
