<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\SpecificationInterface;

class SortedFilter implements SequenceInterface, FilterInterface
{
    /** @var SpecificationInterface[] */
    private array $specifications;

    public function __construct(
        private string $value,
        SpecificationInterface ...$specifications
    ) {
        $this->specifications = $specifications;
    }

    /**
     * @return SpecificationInterface[]
     */
    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function withValue(mixed $value): ?SpecificationInterface
    {
        return $this;
    }
}
