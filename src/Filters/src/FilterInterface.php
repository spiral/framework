<?php

declare(strict_types=1);

namespace Spiral\Filters;

interface FilterInterface
{
    /**
     * Get filtered filter data.
     */
    public function filteredData(): array;
}
