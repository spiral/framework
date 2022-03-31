<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class ScalarValue implements ValueInterface
{
    public function __construct(
        private readonly bool $allowEmpty = false
    ) {
    }

    public function accepts(mixed $value): bool
    {
        return \is_scalar($value) && ($this->allowEmpty || $value !== '');
    }

    public function convert(mixed $value): mixed
    {
        return $value;
    }
}
