<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\ValueInterface;
use Spiral\DataGrid\SpecificationInterface;

final class ValueBetween implements FilterInterface
{
    /** @var ValueInterface|mixed */
    private $expression;

    /** @var string[] */
    private $value;

    /** @var bool */
    private $includeFrom;

    /** @var bool */
    private $includeTo;

    /**
     * @param ValueInterface|mixed $expression
     * @param string[]             $value
     */
    public function __construct($expression, array $value, bool $includeFrom = true, bool $includeTo = true)
    {
        if (is_array($expression) || (is_object($expression) && !$expression instanceof ValueInterface)) {
            throw new ValueException(sprintf(
                'Expression expected to be instance of `%s` or a scalar value, got %s.',
                ValueInterface::class,
                $this->invalidExpressionType($expression)
            ));
        }

        if (!$this->isValidArray($value)) {
            throw new ValueException(sprintf(
                'Value expected to be an array of 2 different scalar elements, got %s.',
                $this->invalidValueType($value)
            ));
        }


        $this->expression = $expression;
        $this->value = array_values($value);
        $this->includeFrom = $includeFrom;
        $this->includeTo = $includeTo;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ?SpecificationInterface
    {
        $between = clone $this;
        if (!$between->expression instanceof ValueInterface) {
            //constant value
            return $between;
        }

        if (!$between->expression->accepts($value)) {
            return null;
        }

        $between->expression = $between->expression->convert($value);

        return $between;
    }

    /**
     * @return ValueInterface|mixed
     */
    public function getValue()
    {
        return $this->expression;
    }

    /**
     * @return string[]
     */
    public function getExpression(): array
    {
        return $this->value;
    }

    /**
     * @return SpecificationInterface[]
     */
    public function getFilters(bool $asOriginal = false): array
    {
        if ($asOriginal && $this->includeFrom && $this->includeTo) {
            return [$this];
        }

        return [$this->fromFilter(), $this->toFilter()];
    }


    private function isValidArray(array $value): bool
    {
        if (count($value) !== 2) {
            return false;
        }

        [$from, $to] = array_values($value);

        return is_scalar($from) && is_scalar($to) && $from !== $to;
    }

    private function invalidValueType(array $value): string
    {
        $count = count($value);
        if ($count === 0) {
            return 'empty array';
        }

        if ($count !== 2) {
            return "array of $count elements";
        }

        [$from, $to] = array_values($value);
        if (!is_scalar($from) || !is_scalar($to)) {
            return 'array of 2 not scalar elements';
        }

        return 'array of 2 same elements';
    }

    /**
     * @param mixed $value
     */
    private function invalidExpressionType($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    private function fromFilter(): FilterInterface
    {
        return $this->includeFrom
            ? new Gte($this->value[1], $this->expression)
            : new Gt($this->value[1], $this->expression);
    }

    private function toFilter(): FilterInterface
    {
        return $this->includeTo
            ? new Lte($this->value[0], $this->expression)
            : new Lt($this->value[0], $this->expression);
    }
}
