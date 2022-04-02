<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class ArrayValue implements ValueInterface
{
    private readonly ValueInterface $base;

    public function __construct(ValueInterface $base)
    {
        $this->base = $base instanceof self ? $base->base : $base;
    }

    public function accepts(mixed $value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $child) {
            if (!$this->base->accepts($child)) {
                return false;
            }
        }

        return $value !== [];
    }

    public function convert(mixed $value): array
    {
        $result = [];
        foreach ($value as $child) {
            $result[] = $this->base->convert($child);
        }

        return $result;
    }
}
