<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class NotEmpty implements ValueInterface
{
    public function __construct(
        private readonly ?ValueInterface $value = null
    ) {
    }

    public function accepts(mixed $value): bool
    {
        return match (true) {
            empty($value) => false,
            $this->value instanceof ValueInterface => $this->value->accepts($value),
            default => true
        };
    }

    public function convert(mixed $value): mixed
    {
        if ($this->value instanceof ValueInterface) {
            return $this->value->convert($value);
        }

        return $value;
    }
}
