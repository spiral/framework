<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Pagination;

use Spiral\DataGrid\SpecificationInterface;

final class Limit implements SpecificationInterface
{
    public function __construct(
        private readonly int $value
    ) {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
