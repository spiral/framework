<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class IntValue implements ValueInterface
{
    public function accepts(mixed $value): bool
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

    public function convert(mixed $value): int
    {
        return (int)$value;
    }
}
