<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation;

use Spiral\Validation\Exceptions\CheckerException;

/**
 * Interface CheckerInterface
 *
 * @package Spiral\Validation
 */
interface CheckerInterface
{
    /**
     * Check value using checker method.
     *
     * @param string $method
     * @param mixed  $value
     * @param array  $arguments
     *
     * @throws CheckerException
     */
    public function check(
        string $method,
        $value,
        array $arguments = []
    );

    /**
     * Version of checker with active local validator.
     *
     * @param ValidatorInterface $validator
     *
     * @return CheckerInterface
     */
    public function withValidator(ValidatorInterface $validator): CheckerInterface;

    /**
     * Return default error message for checker condition.
     *
     * @param string $method
     *
     * @return string
     *
     * @throws CheckerException
     */
    public function getMessage(string $method): string;
}