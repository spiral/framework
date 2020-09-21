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
 * Interface FilterInterface
 *
 * @package Spiral\DataGrid\Specification
 */
interface FilterInterface extends SpecificationInterface
{
    /**
     * Apply the user value to the given filer and return new static version of filter. If input value is not valid
     * the null must be returned.
     *
     * @param mixed $value
     * @return FilterInterface|null
     */
    public function withValue($value): ?SpecificationInterface;
}
