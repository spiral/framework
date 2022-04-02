<?php

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
     */
    public function withValue(mixed $value): ?SpecificationInterface;
}
