<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

abstract class AbstractRule implements RuleInterface
{
    /** @var \SplObjectStorage|ConditionInterface[]|null */
    private $conditions;

    /**
     * @inheritdoc
     */
    public function withConditions(\SplObjectStorage $conditions = null): RuleInterface
    {
        $rule = clone $this;
        $rule->conditions = $conditions;

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function hasConditions(): bool
    {
        return !empty($this->conditions);
    }

    /**
     * @inheritdoc
     */
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
