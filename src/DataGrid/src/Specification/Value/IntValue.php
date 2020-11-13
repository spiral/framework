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
        /**
         * Note: Starting from PHP 8 all whitespaces are ignored when checking
         * if the value is similar to numeric:
         *
         * - <= 7.4 : is_numeric('  -42  ') === false
         * - >= 8.0 : is_numeric('  -42  ') === true
         *
         * Therefore, additional verification is required for compatibility:
         *
         * <code>
         *  $isWhitespaceFramed = trim((string)$value) !== (string)$value;
         * </code>
         */
        return $value === '' || (\is_numeric($value) && \trim((string)$value) === (string)$value);
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
