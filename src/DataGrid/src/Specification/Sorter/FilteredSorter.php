<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

class FilteredSorter implements SequenceInterface, SorterInterface
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

    public function withDirection($direction): ?SpecificationInterface
    {
        return $this;
    }
}
