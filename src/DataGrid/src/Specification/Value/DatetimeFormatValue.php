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

    /** @var string */
    private $convertInto;

    public function __construct(string $readFrom, string $convertInto)
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
     * @return string|null
     */
    public function convert($value): ?string
    {
        try {
            return DateTimeImmutable::createFromFormat($this->readFrom, (string)$value)->format($this->convertInto);
        } catch (Throwable $e) {
            return null;
        }
    }
}
