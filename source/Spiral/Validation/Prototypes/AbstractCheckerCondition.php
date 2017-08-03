<?php

namespace Spiral\Validation\Prototypes;

use Spiral\Validation\CheckerConditionInterface;
use Spiral\Validation\ValidatorInterface;

abstract class AbstractCheckerCondition implements CheckerConditionInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public function withValidator(ValidatorInterface $validator): CheckerConditionInterface
    {
        $condition = clone $this;
        $condition->validator = $validator;

        return $condition;
    }
}