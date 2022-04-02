<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

final class ToLower extends Accessor
{
    protected function acceptsCurrent(mixed $value): bool
    {
        return \is_string($value);
    }

    protected function convertCurrent(mixed $value): mixed
    {
        return \is_string($value) ? \strtolower($value) : $value;
    }
}
