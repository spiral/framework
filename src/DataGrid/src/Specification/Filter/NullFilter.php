<?php

declare(strict_types=1);


namespace Spiral\DataGrid\Specification\Filter;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

class NullFilter implements FilterInterface
{
    public function withValue($value): ?SpecificationInterface
    {
        return null;
    }

    public function getValue()
    {
        return null;
    }
}
