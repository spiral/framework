<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class DatetimeValue implements ValueInterface
{
    public function accepts(mixed $value): bool
    {
        if ($value === '') {
            return true;
        }
        return (\is_numeric($value) || \is_string($value)) && $this->convert($value) !== null;
    }

    public function convert(mixed $value): ?\DateTimeImmutable
    {
        try {
            $value = (string)$value;

            return new \DateTimeImmutable(\is_numeric($value) ? \sprintf('@%s', $value) : $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
