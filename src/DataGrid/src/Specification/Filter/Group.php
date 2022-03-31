<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class Group implements FilterInterface
{
    /** @var FilterInterface[] */
    protected array $filters = [];
    private mixed $value = null;

    abstract public function withValue(mixed $value): ?SpecificationInterface;

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    protected function clone(mixed $value): self
    {
        $group = clone $this;
        $group->filters = [];
        $group->value = $value;

        return $group;
    }
}
