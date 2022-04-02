<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification;

/**
 * Declares the variable for the filters, sorters and etc. To be filled by the user.
 */
interface ValueInterface
{
    /**
     * Must return true if user value can be accepted.
     */
    public function accepts(mixed $value): bool;

    /**
     * Convert value into proper type or apply other filters.
     */
    public function convert(mixed $value): mixed;
}
