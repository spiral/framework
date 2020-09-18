<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

final class ToUpper extends Accessor
{
    /**
     * @inheritDoc
     */
    protected function acceptsCurrent($value): bool
    {
        return is_string($value);
    }

    /**
     * @inheritDoc
     */
    protected function convertCurrent($value)
    {
        return is_string($value) ? strtoupper($value) : $value;
    }
}
