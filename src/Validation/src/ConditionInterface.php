<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * Enables and disabled field validation.
 */
interface ConditionInterface
{
    /**
     * Checks if condition is met and field must be validated by the following rule.
     *
     * @param ValidatorInterface $validator
     * @param string             $field
     * @param mixed              $value
     * @return bool
     */
    public function isMet(ValidatorInterface $validator, string $field, $value): bool;

    /**
     * @param null|array $options
     * @return ConditionInterface
     */
    public function withOptions(?array $options): ConditionInterface;
}
