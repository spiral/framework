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

final class Between implements FilterInterface
{
    /** @var string */
    private $expression;

    /** @var ValueInterface|array */
    private $value;

    /** @var bool */
    private $includeFrom;

    /** @var bool */
    private $includeTo;

    /**
     * @param ValueInterface|array $value
     */
    public function __construct(string $expression, $value, bool $includeFrom = true, bool $includeTo = true)
    {
        if (!$value instanceof ValueInterface && !$this->isValidArray($value)) {
            throw new ValueException(sprintf(
                'Value expected to be instance of `%s` or an array of 2 different elements, got %s.',
                ValueInterface::class,
                $this->invalidValueType($value)
            ));
        }

        $this->expression = $expression;
        $this->value = $this->convertValue($value);
        $this->includeFrom = $includeFrom;
        $this->includeTo = $includeTo;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ?SpecificationInterface
    {
        $between = clone $this;
        if (!$between->value instanceof ValueInterface) {
            // constant value
            return $between;
        }

        if (!$this->isValidArray($value)) {
            // only array of 2 values is expected
            return null;
        }

        [$from, $to] = $this->convertValue($value);
        if (!$between->value->accepts($from) || !$between->value->accepts($to)) {
            return null;
        }

        $between->value = [$between->value->convert($from), $between->value->convert($to)];

        return $between;
    }

    /**
     * @inheritDoc
     * @return ValueInterface|array
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getExpression(): string
    {
        return $this->expression;
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


    /**
     * @param mixed|array $value
     */
    private function isValidArray($value): bool
    {
        if (!is_array($value) || count($value) !== 2) {
            return false;
        }

        [$from, $to] = array_values($value);

        return $from !== $to;
    }

    /**
     * @param mixed $value
     */
    private function invalidValueType($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            $count = count($value);
            if ($count === 0) {
                return 'empty array';
            }

            if ($count !== 2) {
                return "array of $count elements";
            }

            return 'array of 2 same elements';
        }

        return gettype($value);
    }

    /**
     * @param ValueInterface|array $value
     * @return ValueInterface|array
     */
    private function convertValue($value)
    {
        if ($value instanceof ValueInterface) {
            return $value;
        }

        $values = array_values($value);
        if ($values[1] < $values[0]) {
            return [$values[1], $values[0]];
        }

        return $values;
    }

    private function fromFilter(): FilterInterface
    {
        $value = $this->value instanceof ValueInterface ? $this->value : $this->value[0];

        return $this->includeFrom
            ? new Gte($this->expression, $value)
            : new Gt($this->expression, $value);
    }

    private function toFilter(): FilterInterface
    {
        $value = $this->value instanceof ValueInterface ? $this->value : $this->value[1];

        return $this->includeTo
            ? new Lte($this->expression, $value)
            : new Lt($this->expression, $value);
    }
}
