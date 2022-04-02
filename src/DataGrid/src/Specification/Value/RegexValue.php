<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

class RegexValue implements ValueInterface
{
    public function __construct(
        private string $pattern
    ) {
    }

    public function accepts(mixed $value): bool
    {
        return (\is_numeric($value) || \is_string($value)) && $this->isValid($this->convert($value));
    }

    public function convert(mixed $value): string
    {
        return (string)$value;
    }

    private function isValid(string $value): bool
    {
        return (bool)\preg_match($this->pattern, $value);
    }
}
