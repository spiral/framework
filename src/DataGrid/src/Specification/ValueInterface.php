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

/**
 * Declares the variable for the filters, sorters and etc. To be filled by the user.
 */
interface ValueInterface
{
    /**
     * Must return true if user value can be accepted.
     *
     * @param mixed $value
     * @return bool
     */
    public function accepts($value): bool;

    /**
     * Convert value into proper type or apply other filters.
     *
     * @param mixed $value
     * @return mixed
     */
    public function convert($value);
}
