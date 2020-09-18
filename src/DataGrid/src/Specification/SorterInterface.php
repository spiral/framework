<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

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
     *
     * @param mixed $direction
     * @return SorterInterface|null
     */
    public function withDirection($direction): ?SpecificationInterface;
}
