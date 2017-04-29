<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation;

use Spiral\Validation\Exceptions\ValidationException;

/**
 * Validators responsible for data validations. Validation rules are implementation dependent but
 * should always be specified in array form relative to validator implementation.
 *
 * Future updates: it's planned to make validator immutable in very far future.
 */
interface ValidatorInterface
{
    /**
     * Update validation rules.
     *
     * @param array $rules
     *
     * @return self
     */
    public function setRules(array $rules): ValidatorInterface;

    /**
     * Update validation data (context). Data change must reset validation state and all errors.
     *
     * @param array|\ArrayAccess|\Spiral\Models\EntityInterface $data
     *
     * @return self
     *
     * @throws ValidationException
     */
    public function setData($data): ValidatorInterface;

    /**
     * Get all validation data passed into validator.
     *
     * @return array|\ArrayAccess
     */
    public function getData();

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
     * Register outer validation error. Registered error persists until context data are changed
     * or flushRegistered method not called.
     *
     * @param string $field
     * @param string $error
     *
     * @return self
     */
    public function registerError(string $field, string $error): ValidatorInterface;

    /**
     * Reset validation state.
     */
    public function reset();

    /**
     * Flush all registered errors.
     */
    public function flushRegistered();

    /**
     * Check if context data valid accordingly to provided rules.
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function isValid(): bool;

    /**
     * Evil tween of isValid() method should return true if context data is not valid.
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function hasErrors(): bool;

    /**
     * List of errors associated with parent field, every field should have only one error assigned.
     *
     * @return array
     */
    public function getErrors(): array;
}
