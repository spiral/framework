<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification;

use Spiral\DataGrid\SpecificationInterface;

/**
 * The specification to configure the sorting direction of the data source.
 */
interface SorterInterface extends SpecificationInterface
{
    // available directions
    public const ASC  = 'asc';
    public const DESC = 'desc';

    /**
     * Lock the sorter to the specific sorting direction.
     */
    public function withDirection(string $direction): ?SpecificationInterface;
}
