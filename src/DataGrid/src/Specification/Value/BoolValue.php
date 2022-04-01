<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

final class BoolValue implements ValueInterface
{
    public function accepts(mixed $value): bool
    {
        if (\is_bool($value)) {
            return true;
        }

        if (\is_scalar($value)) {
            return \in_array(\strtolower((string)$value), ['0', '1', 'true', 'false'], true);
        }

        return false;
    }

    public function convert(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_scalar($value)) {
            return match (\strtolower((string)$value)) {
                '0', 'false' => false,
                '1', 'true' => true,
            };
        }

        throw new ValueException(\sprintf(
            'Value is expected to be boolean, got `%s`. Check the value with `accepts()` method first.',
            \get_debug_type($value)
        ));
    }
}
