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
     * @param mixed  $value
     *
     */
    public function ignoreEmpty(string $method, $value, array $args): bool;

    /**
     * Check value using checker method.
     *
     * @param mixed              $value
     *
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
     * @param mixed  $value
     *
     *
     * @throws CheckerException
     */
    public function getMessage(string $method, string $field, $value, array $arguments = []): string;
}
