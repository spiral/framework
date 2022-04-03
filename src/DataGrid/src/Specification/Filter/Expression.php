<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\ValueInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class Expression implements FilterInterface
{
    public function __construct(
        protected string $expression,
        protected mixed $value
    ) {
    }

    public function withValue(mixed $value): ?SpecificationInterface
    {
        $filter = clone $this;
        if (!$filter->value instanceof ValueInterface) {
            // constant value
            return $filter;
        }

        if (!$filter->value->accepts($value)) {
            // invalid value
            return null;
        }

        // create static filtered value
        $filter->value = $filter->value->convert($value);

        return $filter;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
