<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\SpecificationInterface;

class SortedFilter implements SequenceInterface, FilterInterface
{
    private $value;

    /** @var SpecificationInterface[] */
    private $specifications;

    public function __construct(string $value, SpecificationInterface ...$specifications)
    {
        $this->value = $value;
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

    public function withValue($value): ?SpecificationInterface
    {
        return $this;
    }
}
