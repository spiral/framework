<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\ValueInterface;
use Spiral\DataGrid\SpecificationInterface;

final class Between implements FilterInterface
{
    private array|ValueInterface $value;

    public function __construct(
        private readonly string $expression,
        array|ValueInterface $value,
        private readonly bool $includeFrom = true,
        private readonly bool $includeTo = true
    ) {
        if (!$value instanceof ValueInterface && !$this->isValidArray($value)) {
            throw new ValueException(\sprintf(
                'Value expected to be instance of `%s` or an array of 2 different elements, got %s.',
                ValueInterface::class,
                $this->invalidValueType($value)
            ));
        }
        $this->value = $this->convertValue($value);
    }

    public function withValue(mixed $value): ?SpecificationInterface
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

    public function getValue(): array|ValueInterface
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

    private function isValidArray(mixed $value): bool
    {
        if (!\is_array($value) || \count($value) !== 2) {
            return false;
        }

        [$from, $to] = \array_values($value);

        return $from !== $to;
    }

    private function invalidValueType(mixed $value): string
    {
        if (\is_object($value)) {
            return $value::class;
        }

        if (\is_array($value)) {
            $count = \count($value);
            if ($count === 0) {
                return 'empty array';
            }

            if ($count !== 2) {
                return \sprintf('array of %s elements', $count);
            }

            return 'array of 2 same elements';
        }

        return \gettype($value);
    }

    private function convertValue(array|ValueInterface $value): array|ValueInterface
    {
        if ($value instanceof ValueInterface) {
            return $value;
        }

        $values = \array_values($value);
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
