<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

/**
 * @example new Gt('field', new RangeValue(new IntValue(), null, RangeValue\Boundary::excluding(12)))
 * will mean that 'field' value should be greater than the input but only if the input is less than 12.
 */
final class RangeValue implements ValueInterface
{
    /** @var ValueInterface */
    private $base;

    /** @var RangeValue\Boundary */
    private $from;

    /** @var RangeValue\Boundary */
    private $to;

    /**
     * @param ValueInterface           $base
     * @param RangeValue\Boundary|null $from
     * @param RangeValue\Boundary|null $to
     */
    public function __construct(ValueInterface $base, RangeValue\Boundary $from = null, RangeValue\Boundary $to = null)
    {
        $this->base = $base;
        $from = $from ?? RangeValue\Boundary::empty();
        $to = $to ?? RangeValue\Boundary::empty();

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

    /**
     * @param RangeValue\Boundary $from
     * @param RangeValue\Boundary $to
     */
    private function validateBoundaries(RangeValue\Boundary $from, RangeValue\Boundary $to): void
    {
        if (!$this->acceptsBoundary($from) || !$this->acceptsBoundary($to)) {
            throw new ValueException('Range boundaries should be applicable via passed type.');
        }

        if ($this->convertBoundaryValue($from) === $this->convertBoundaryValue($to)) {
            throw new ValueException('Range boundaries should be different.');
        }
    }

    /**
     * @param RangeValue\Boundary $boundary
     * @return bool
     */
    private function acceptsBoundary(RangeValue\Boundary $boundary): bool
    {
        return $boundary->empty || $this->base->accepts($boundary->value);
    }

    /**
     * @param RangeValue\Boundary $boundary
     * @return mixed|null
     */
    private function convertBoundaryValue(RangeValue\Boundary $boundary)
    {
        return $boundary->empty ? null : $this->base->convert($boundary->value);
    }

    /**
     * @param mixed $value
     * @return bool
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
     * @return bool
     */
    private function acceptsTo($value): bool
    {
        if ($this->to->empty) {
            return true;
        }

        $to = $this->base->convert($this->to->value);

        return $this->to->include ? ($value <= $to) : ($value < $to);
    }

    /**
     * @param RangeValue\Boundary $from
     * @param RangeValue\Boundary $to
     */
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
