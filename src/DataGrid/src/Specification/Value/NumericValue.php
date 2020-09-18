<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

final class NumericValue implements ValueInterface
{
    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return is_numeric($value) || (is_string($value) && $value === '');
    }

    /**
     * @inheritDoc
     * @return int|float|double
     */
    public function convert($value)
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        throw new ValueException(sprintf(
            'Value is expected to be numeric, got `%s`. Check the value with `accepts()` method first.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
