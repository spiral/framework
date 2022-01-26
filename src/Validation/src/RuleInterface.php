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
     */
    public function ignoreEmpty($value): bool;

    /**
     * @param \SplObjectStorage|null $conditions
     */
    public function withConditions(\SplObjectStorage $conditions = null): RuleInterface;

    public function hasConditions(): bool;

    /**
     * Conditions associated with the rule.
     *
     * @return \Generator|ConditionInterface[]
     */
    public function getConditions(): \Generator;

    /**
     * @param mixed              $value
     *
     */
    public function validate(ValidatorInterface $v, string $field, $value): bool;

    /**
     * Get validation error message.
     *
     * @param mixed  $value
     *
     */
    public function getMessage(string $field, $value): string;
}
