<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\ValueInterface;
use Spiral\DataGrid\SpecificationInterface;

final class ValueBetween implements FilterInterface
{
    /** @var string[] */
    private readonly array $value;

    /**
     * @param string[] $value
     */
    public function __construct(
        private ValueInterface|string|int|float $expression,
        array $value,
        private readonly bool $includeFrom = true,
        private readonly bool $includeTo = true
    ) {
        if (!$this->isValidArray($value)) {
            throw new ValueException(\sprintf(
                'Value expected to be an array of 2 different scalar elements, got %s.',
                $this->invalidValueType($value)
            ));
        }
        $this->value = \array_values($value);
    }

    public function withValue(mixed $value): ?SpecificationInterface
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

    public function getValue(): mixed
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
        if (\count($value) !== 2) {
            return false;
        }

        [$from, $to] = \array_values($value);

        return \is_scalar($from) && \is_scalar($to) && $from !== $to;
    }

    private function invalidValueType(array $value): string
    {
        $count = \count($value);
        if ($count === 0) {
            return 'empty array';
        }

        if ($count !== 2) {
            return \sprintf('array of %s elements', $count);
        }

        [$from, $to] = \array_values($value);
        if (!\is_scalar($from) || !\is_scalar($to)) {
            return 'array of 2 not scalar elements';
        }

        return 'array of 2 same elements';
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
