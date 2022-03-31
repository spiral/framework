<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\Value\RangeValue\Boundary;
use Spiral\DataGrid\Specification\ValueInterface;

/**
 * @example new Gt('field', new RangeValue(new IntValue(), null, RangeValue\Boundary::excluding(12)))
 * will mean that 'field' value should be greater than the input but only if the input is less than 12.
 */
final class RangeValue implements ValueInterface
{
    private Boundary $from;
    private Boundary $to;

    public function __construct(
        private readonly ValueInterface $base,
        RangeValue\Boundary $from = null,
        RangeValue\Boundary $to = null
    ) {
        $from ??= RangeValue\Boundary::empty();
        $to ??= RangeValue\Boundary::empty();

        $this->validateBoundaries($from, $to);
        $this->setBoundaries($from, $to);
    }

    public function accepts(mixed $value): bool
    {
        return $this->base->accepts($value) && $this->acceptsFrom($value) && $this->acceptsTo($value);
    }

    public function convert(mixed $value): mixed
    {
        return $this->base->convert($value);
    }

    private function validateBoundaries(RangeValue\Boundary $from, RangeValue\Boundary $to): void
    {
        if (!$this->acceptsBoundary($from) || !$this->acceptsBoundary($to)) {
            throw new ValueException('Range boundaries should be applicable via passed type.');
        }

        if ($this->convertBoundaryValue($from) === $this->convertBoundaryValue($to)) {
            throw new ValueException('Range boundaries should be different.');
        }
    }

    private function acceptsBoundary(RangeValue\Boundary $boundary): bool
    {
        return $boundary->empty || $this->base->accepts($boundary->value);
    }

    private function convertBoundaryValue(RangeValue\Boundary $boundary)
    {
        return $boundary->empty ? null : $this->base->convert($boundary->value);
    }

    private function acceptsFrom(mixed $value): bool
    {
        if ($this->from->empty) {
            return true;
        }

        $from = $this->base->convert($this->from->value);

        return $this->from->include ? ($value >= $from) : ($value > $from);
    }

    private function acceptsTo(mixed $value): bool
    {
        if ($this->to->empty) {
            return true;
        }

        $to = $this->base->convert($this->to->value);

        return $this->to->include ? ($value <= $to) : ($value < $to);
    }

    private function setBoundaries(RangeValue\Boundary $from, RangeValue\Boundary $to): void
    {
        //Swap if from < to and both not empty
        if (!$from->empty && !$to->empty && $from->value > $to->value) {
            [$from, $to] = [$to, $from];
        }

        $this->from = $from;
        $this->to = $to;
    }
}
