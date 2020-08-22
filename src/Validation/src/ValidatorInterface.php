<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
     *
     * @param iterable $data
     * @return ValidatorInterface
     */
    public function withData($data): ValidatorInterface;

    /**
     * Receive field from context data or return default value.
     *
     * @param string $field
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValue(string $field, $default = null);

    /**
     * Check if field is provided in the given data.
     *
     * @param string $field
     * @return bool
     */
    public function hasValue(string $field): bool;

    /**
     * Create new validator instance with new context.
     *
     * @param mixed $context
     * @return ValidatorInterface
     */
    public function withContext($context): ValidatorInterface;

    /**
     * Get context data (not validated).
     *
     * @return mixed
     */
    public function getContext();

    /**
     * Check if context data valid accordingly to provided rules.
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function isValid(): bool;

    /**
     * List of errors associated with parent field, every field should have only one error assigned.
     *
     * @return array
     *
     * @throws ValidationException
     */
    public function getErrors(): array;
}
