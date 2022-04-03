<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

final class EnumValue implements ValueInterface
{
    private readonly array $values;

    public function __construct(
        private readonly ValueInterface $base,
        mixed ...$values
    ) {
        if ($base instanceof self) {
            throw new ValueException(\sprintf('Nested value type not allowed, got `%s`', $base::class));
        }

        $this->values = $this->convertEnum(\array_unique($values));
    }

    public function accepts(mixed $value): bool
    {
        if (!$this->base->accepts($value)) {
            return false;
        }

        return \in_array($this->base->convert($value), $this->values, true);
    }

    public function convert(mixed $value): mixed
    {
        return $this->base->convert($value);
    }

    private function convertEnum(array $values): array
    {
        if (empty($values)) {
            throw new ValueException('Enum set should not be empty');
        }

        $type = new ArrayValue($this->base);
        if (!$type->accepts($values)) {
            throw new ValueException(
                \sprintf(
                    '"Got non-compatible values, expected only compatible with `%s`.',
                    $this->base::class
                )
            );
        }

        return $type->convert($values);
    }
}
