<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\CheckerException;

interface CheckerInterface
{
    /**
     * Return true if validation is required for given method. Used to skip validation for empty
     * values.
     */
    public function ignoreEmpty(string $method, mixed $value, array $args): bool;

    /**
     * Check value using checker method.
     *
     * @throws CheckerException
     */
    public function check(
        ValidatorInterface $v,
        string $method,
        string $field,
        mixed $value,
        array $args = []
    ): bool;

    /**
     * Return error message associated with check method.
     *
     * @throws CheckerException
     */
    public function getMessage(string $method, string $field, mixed $value, array $arguments = []): string;
}
