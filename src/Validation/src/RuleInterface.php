<?php

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
     */
    public function ignoreEmpty(mixed $value): bool;

    public function withConditions(\SplObjectStorage $conditions = null): RuleInterface;

    public function hasConditions(): bool;

    /**
     * Conditions associated with the rule.
     *
     * @return \Generator|ConditionInterface[]
     */
    public function getConditions(): \Generator;

    public function validate(ValidatorInterface $v, string $field, mixed $value): bool;

    /**
     * Get validation error message.
     */
    public function getMessage(string $field, mixed $value): string;
}
