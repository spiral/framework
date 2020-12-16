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

final class DatetimeFormatValue implements ValueInterface
{
    /** @var string */
    private $readFrom;

    /** @var string|null */
    private $convertInto;

    public function __construct(string $readFrom, ?string $convertInto = null)
    {
        $this->readFrom = $readFrom;
        $this->convertInto = $convertInto;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return is_string($value) && $this->convert($value) !== null;
    }

    /**
     * @inheritDoc
     * @return string|null|DateTimeImmutable
     */
    public function convert($value)
    {
        try {
            $datetime = DateTimeImmutable::createFromFormat($this->readFrom, (string)$value);
            if (!$datetime instanceof DateTimeImmutable) {
                return null;
            }

            if ($this->convertInto !== null) {
                $formatted = $datetime->format($this->convertInto);
                return is_string($formatted) ? $formatted : null;
            }

            return $datetime;
        } catch (Throwable $e) {
            return null;
        }
    }
}
