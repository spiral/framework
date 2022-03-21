<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class SubsetValue implements ValueInterface
{
    private readonly ValueInterface $enum;

    public function __construct(ValueInterface $enum, mixed ...$values)
    {
        $this->enum = new EnumValue($enum, ...$values);
    }

    public function accepts(mixed $value): bool
    {
        $value = (array)$value;

        if (\count($value) === 1) {
            return $this->enum->accepts(\array_values($value)[0]);
        }

        if (empty($value)) {
            return false;
        }

        return $this->arrayType()->accepts($value);
    }

    public function convert(mixed $value): mixed
    {
        return $this->arrayType()->convert((array)$value);
    }

    private function arrayType(): ArrayValue
    {
        return new ArrayValue($this->enum);
    }
}
