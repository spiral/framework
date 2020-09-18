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

use Spiral\DataGrid\Specification\ValueInterface;

final class IntValue implements ValueInterface
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
     * @return int
     */
    public function convert($value): int
    {
        return (int)$value;
    }
}
