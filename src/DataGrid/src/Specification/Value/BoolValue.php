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

final class BoolValue implements ValueInterface
{
    /**
     * @inheritDoc
     * @return bool
     */
    public function accepts($value): bool
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_scalar($value)) {
            return in_array(strtolower((string)$value), ['0', '1', 'true', 'false'], true);
        }

        return false;
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function convert($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            switch (strtolower((string)$value)) {
                case '0':
                case 'false':
                    return false;

                case '1':
                case 'true':
                    return true;
            }
        }

        throw new ValueException(sprintf(
            'Value is expected to be boolean, got `%s`. Check the value with `accepts()` method first.',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
