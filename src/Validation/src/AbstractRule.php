<?php

declare(strict_types=1);

namespace Spiral\Validation;

abstract class AbstractRule implements RuleInterface
{
    private array|\SplObjectStorage|null $conditions = null;

    public function withConditions(\SplObjectStorage $conditions = null): RuleInterface
    {
        $rule = clone $this;
        $rule->conditions = $conditions;

        return $rule;
    }

    public function hasConditions(): bool
    {
        return !empty($this->conditions);
    }

    public function getConditions(): \Generator
    {
        if (empty($this->conditions)) {
            return;
        }

        foreach ($this->conditions as $condition) {
            yield $condition->withOptions($this->conditions->offsetGet($condition));
        }
    }
}
