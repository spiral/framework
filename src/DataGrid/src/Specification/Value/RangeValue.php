<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\Value\RangeValue\Boundary;
use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

/**
 * @example new Gt('field', new RangeValue(new IntValue(), null, RangeValue\Boundary::excluding(12)))
 * will mean that 'field' value should be greater than the input but only if the input is less than 12.
 */
final class RangeValue implements ValueInterface
{
    private ValueInterface $base;

    private Boundary $from;

    private Boundary $to;

    /**
     * @param RangeValue\Boundary|null $from
     * @param RangeValue\Boundary|null $to
     */
    public function __construct(ValueInterface $base, Boundary $from = null, Boundary $to = null)
    {
        $this->base = $base;
        $from ??= Boundary::empty();
        $to ??= Boundary::empty();

        $this->validateBoundaries($from, $to);
        $this->setBoundaries($from, $to);
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return $this->base->accepts($value) && $this->acceptsFrom($value) && $this->acceptsTo($value);
    }

    /**
     * @inheritDoc
     */
    public function convert($value)
    {
        return $this->base->convert($value);
    }

    private function validateBoundaries(Boundary $from, Boundary $to): void
    {
        if (!$this->acceptsBoundary($from) || !$this->acceptsBoundary($to)) {
            throw new ValueException('Range boundaries should be applicable via passed type.');
        }

        if ($this->convertBoundaryValue($from) === $this->convertBoundaryValue($to)) {
            throw new ValueException('Range boundaries should be different.');
        }
    }

    private function acceptsBoundary(Boundary $boundary): bool
    {
        return $boundary->empty || $this->base->accepts($boundary->value);
    }

    /**
     * @return mixed|null
     */
    private function convertBoundaryValue(Boundary $boundary)
    {
        return $boundary->empty ? null : $this->base->convert($boundary->value);
    }

    /**
     * @param mixed $value
     */
    private function acceptsFrom($value): bool
    {
        if ($this->from->empty) {
            return true;
        }

        $from = $this->base->convert($this->from->value);

        return $this->from->include ? ($value >= $from) : ($value > $from);
    }

    /**
     * @param mixed $value
     */
    private function acceptsTo($value): bool
    {
        if ($this->to->empty) {
            return true;
        }

        $to = $this->base->convert($this->to->value);

        return $this->to->include ? ($value <= $to) : ($value < $to);
    }

    private function setBoundaries(Boundary $from, Boundary $to): void
    {
        //Swap if from < to and both not empty
        if (!$from->empty && !$to->empty && $from->value > $to->value) {
            [$from, $to] = [$to, $from];
        }

        $this->from = $from;
        $this->to = $to;
    }
}
