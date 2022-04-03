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
        $value = (array) $value;

        return match (true) {
            \count($value) === 1 => $this->enum->accepts(\current($value)),
            empty($value) => false,
            default => $this->arrayType()->accepts($value)
        };
    }

    public function convert(mixed $value): array
    {
        return $this->arrayType()->convert((array)$value);
    }

    private function arrayType(): ArrayValue
    {
        return new ArrayValue($this->enum);
    }
}
