<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class StringValue implements ValueInterface
{
    public function __construct(
        private readonly bool $allowEmpty = false
    ) {
    }

    public function accepts(mixed $value): bool
    {
        return (\is_numeric($value) || \is_string($value)) && ($this->allowEmpty || $this->convert($value) !== '');
    }

    public function convert(mixed $value): string
    {
        return (string)$value;
    }
}
