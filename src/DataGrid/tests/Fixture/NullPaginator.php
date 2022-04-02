<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Fixture;

use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

class NullPaginator implements FilterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getValue(): mixed
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withValue($value): ?SpecificationInterface
    {
        return null;
    }
}
