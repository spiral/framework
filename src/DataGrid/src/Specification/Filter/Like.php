<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\Value\StringValue;

class Like extends Expression
{
    public function __construct(
        string $expression,
        mixed $value = null,
        private readonly string $pattern = '%%%s%%'
    ) {
        parent::__construct($expression, $value ?? new StringValue());
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
