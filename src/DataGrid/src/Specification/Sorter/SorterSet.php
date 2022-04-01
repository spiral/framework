<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

final class SorterSet implements SorterInterface
{
    private array $sorters;

    public function __construct(SorterInterface ...$sorters)
    {
        $this->sorters = $sorters;
    }

    public function withDirection(string $direction): SpecificationInterface
    {
        $sorter = clone $this;
        $sorter->sorters = [];

        foreach ($this->sorters as $s) {
            $sorter->sorters[] = $s->withDirection($direction);
        }

        return $sorter;
    }

    /**
     * @return SorterInterface[]
     */
    public function getSorters(): array
    {
        return $this->sorters;
    }

    public function getValue(): string
    {
        return '1';
    }
}
