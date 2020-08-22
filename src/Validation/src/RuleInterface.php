<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

interface RuleInterface
{
    /**
     * Must return true if rule does expect to validate empty values.
     *
     * Example:
     * ["notEmpty", "email"] // fails on empty
     * ["email"]             // passed empty values
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function ignoreEmpty($value): bool;

    /**
     * @param \SplObjectStorage|null $conditions
     * @return RuleInterface
     */
    public function withConditions(\SplObjectStorage $conditions = null): RuleInterface;

    /**
     * @return bool
     */
    public function hasConditions(): bool;

    /**
     * Conditions associated with the rule.
     *
     * @return \Generator|ConditionInterface[]
     */
    public function getConditions(): \Generator;

    /**
     * @param ValidatorInterface $v
     * @param string             $field
     * @param mixed              $value
     *
     * @return bool
     */
    public function validate(ValidatorInterface $v, string $field, $value): bool;

    /**
     * Get validation error message.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string
     */
    public function getMessage(string $field, $value): string;
}
