<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class AbstractSorter implements SorterInterface
{
    private array $expressions;

    public function __construct(string ...$expressions)
    {
        $this->expressions = $expressions;
    }

    public function withDirection(string $direction): SpecificationInterface
    {
        return $this;
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }
}
