<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\CheckerException;

interface CheckerInterface
{
    /**
     * Return true if validation is required for given method. Used to skip validation for empty
     * values.
     *
     * @param string $method
     * @param mixed  $value
     * @param array  $args
     *
     * @return bool
     */
    public function ignoreEmpty(string $method, $value, array $args): bool;

    /**
     * Check value using checker method.
     *
     * @param ValidatorInterface $v
     * @param string             $method
     * @param string             $field
     * @param mixed              $value
     * @param array              $args
     *
     * @return bool
     *
     * @throws CheckerException
     */
    public function check(
        ValidatorInterface $v,
        string $method,
        string $field,
        $value,
        array $args = []
    ): bool;

    /**
     * Return error message associated with check method.
     *
     * @param string $method
     * @param string $field
     * @param mixed  $value
     * @param array  $arguments
     *
     * @return string
     *
     * @throws CheckerException
     */
    public function getMessage(string $method, string $field, $value, array $arguments = []): string;
}
