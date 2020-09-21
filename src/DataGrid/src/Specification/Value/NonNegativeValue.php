<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

final class NonNegativeValue extends CompareValue
{
    /**
     * @inheritDoc
     */
    protected function compare($value): bool
    {
        return $value >= 0;
    }
}
