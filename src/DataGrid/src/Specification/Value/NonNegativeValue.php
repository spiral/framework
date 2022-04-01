<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

final class NonNegativeValue extends CompareValue
{
    protected function compare(mixed $value): bool
    {
        return $value >= 0;
    }
}
