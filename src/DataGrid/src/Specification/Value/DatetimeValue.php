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

use DateTimeImmutable;
use Spiral\DataGrid\Specification\ValueInterface;
use Throwable;

final class DatetimeValue implements ValueInterface
{
    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return (is_numeric($value) || is_string($value)) && ($this->convert($value) !== null || (string)$value === '');
    }

    /**
     * @inheritDoc
     * @return DateTimeImmutable|null
     */
    public function convert($value): ?DateTimeImmutable
    {
        try {
            $value = (string)$value;

            return new DateTimeImmutable(is_numeric($value) ? "@$value" : $value);
        } catch (Throwable $e) {
            return null;
        }
    }
}
