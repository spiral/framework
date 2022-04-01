<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\Value\ArrayValue;
use Spiral\DataGrid\Specification\ValueInterface;

class NotInArray extends Expression
{
    public function __construct(string $expression, mixed $value, bool $wrapInArray = true)
    {
        parent::__construct(
            $expression,
            $value instanceof ValueInterface && $wrapInArray ? new ArrayValue($value) : $value
        );
    }
}
