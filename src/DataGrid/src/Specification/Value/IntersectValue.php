<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class IntersectValue implements ValueInterface
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

        foreach ($value as $v) {
            if ($this->enum->accepts($v)) {
                return true;
            }
        }

        return false;
    }

    public function convert(mixed $value): array
    {
        $result = [];
        foreach ((array)$value as $v) {
            if ($this->enum->accepts($v)) {
                $result[] = $this->enum->convert($v);
            }
        }

        return $result;
    }
}
