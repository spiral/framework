<?php

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

class AnyOfCondition extends AbstractCondition
{
    public function __construct(
        private Compositor $compositor
    ) {
    }

    public function isMet(ValidatorInterface $validator, string $field, mixed $value): bool
    {
        if (empty($this->options)) {
            return true;
        }

        foreach ($this->compositor->makeConditions($field, $this->options) as $condition) {
            if ($condition->isMet($validator, $field, $value)) {
                return true;
            }
        }

        return false;
    }
}
