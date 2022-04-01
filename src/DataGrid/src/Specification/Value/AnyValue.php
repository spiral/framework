<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class AnyValue implements ValueInterface
{
    public function accepts(mixed $value): bool
    {
        return true;
    }

    public function convert(mixed $value): mixed
    {
        return $value;
    }
}
