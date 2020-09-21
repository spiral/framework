<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\RangeValue;

class Boundary
{
    /** @var mixed */
    public $value;

    /** @var bool */
    public $include = false;

    /** @var bool */
    public $empty = false;

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function empty(): self
    {
        return self::create(null, true);
    }

    /**
     * @param mixed|null $value
     * @return self
     */
    public static function including($value): self
    {
        return self::create($value, true);
    }

    /**
     * @param mixed|null $value
     * @return self
     */
    public static function excluding($value): self
    {
        return self::create($value, false);
    }

    /**
     * @param mixed|null $value
     * @param bool       $include
     * @return self
     */
    private static function create($value, bool $include): self
    {
        $self = new self();
        $self->value = $value;
        $self->empty = ($value === null);
        $self->include = $include;

        return $self;
    }
}
