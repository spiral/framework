<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\RangeValue;

class Boundary
{
    public mixed $value;
    public bool $include = false;
    public bool $empty = false;

    private function __construct()
    {
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
        $self = new self();
        $self->value = $value;
        $self->empty = ($value === null);
        $self->include = $include;

        return $self;
    }
}
