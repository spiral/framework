<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class Group implements FilterInterface
{
    /** @var FilterInterface[] */
    protected $filters;

    /** @var mixed */
    private $value;

    /**
     * @inheritDoc
     */
    abstract public function withValue($value): ?SpecificationInterface;

    /**
     * @return array|FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    protected function clone($value): self
    {
        $group = clone $this;
        $group->filters = [];
        $group->value = $value;

        return $group;
    }
}
