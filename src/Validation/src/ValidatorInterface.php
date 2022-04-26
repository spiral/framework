<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\ValidationException;

/**
 * Singular validation state (with data, context and rules encapsulated).
 */
interface ValidatorInterface
{
    /**
     * Create validator copy with new data set.
     */
    public function withData(array|object $data): ValidatorInterface;

    /**
     * Receive field from context data or return default value.
     */
    public function getValue(string $field, mixed $default = null): mixed;

    /**
     * Check if field is provided in the given data.
     */
    public function hasValue(string $field): bool;

    /**
     * Create new validator instance with new context.
     */
    public function withContext(mixed $context): ValidatorInterface;

    /**
     * Get context data (not validated).
     */
    public function getContext(): mixed;

    /**
     * Check if context data valid accordingly to provided rules.
     *
     * @throws ValidationException
     */
    public function isValid(): bool;

    /**
     * List of errors associated with parent field, every field should have only one error assigned.
     *
     * @return array<string, string> Keys are fields, values are messages
     *
     * @throws ValidationException
     */
    public function getErrors(): array;
}
