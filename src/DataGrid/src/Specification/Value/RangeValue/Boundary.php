<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\RangeValue;

class Boundary
{
    private function __construct(
        public mixed $value,
        public bool $include,
        public bool $empty,
    ) {
    }

    public static function empty(): self
    {
        return self::create(null, true);
    }

    public static function including(mixed $value): self
    {
        return self::create($value, true);
    }

    public static function excluding(mixed $value): self
    {
        return self::create($value, false);
    }

    private static function create(mixed $value, bool $include): self
    {
        return new self($value, $include, $value === null);
    }
}
