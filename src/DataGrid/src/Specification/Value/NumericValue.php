<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

final class NumericValue implements ValueInterface
{
    public function accepts(mixed $value): bool
    {
        return \is_numeric($value) || $value === '';
    }

    public function convert(mixed $value): float|int
    {
        if (\is_numeric($value)) {
            return $value + 0;
        }

        throw new ValueException(\sprintf(
            'Value is expected to be numeric, got `%s`. Check the value with `accepts()` method first.',
            \get_debug_type($value)
        ));
    }
}
