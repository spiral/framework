<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

final class Sorter implements SorterInterface
{
    private readonly DirectionalSorter $sorter;

    public function __construct(string ...$expressions)
    {
        $this->sorter = new DirectionalSorter(new AscSorter(...$expressions), new DescSorter(...$expressions));
    }

    public function withDirection(string $direction): ?SpecificationInterface
    {
        $sorter = clone $this;

        return $sorter->sorter->withDirection($direction);
    }

    public function getValue(): ?string
    {
        return $this->sorter->getValue();
    }
}
