<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class DatetimeFormatValue implements ValueInterface
{
    public function __construct(
        private readonly string $readFrom,
        private readonly ?string $convertInto = null
    ) {
    }

    public function accepts(mixed $value): bool
    {
        return \is_string($value) && $this->convert($value) !== null;
    }

    public function convert(mixed $value): string|null|\DateTimeInterface
    {
        try {
            $datetime = \DateTimeImmutable::createFromFormat($this->readFrom, (string)$value);
            if (!$datetime instanceof \DateTimeImmutable) {
                return null;
            }

            if ($this->convertInto !== null) {
                $formatted = $datetime->format($this->convertInto);
                return \is_string($formatted) ? $formatted : null;
            }

            return $datetime;
        } catch (\Throwable) {
            return null;
        }
    }
}
