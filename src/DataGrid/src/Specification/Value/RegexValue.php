<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

class RegexValue implements ValueInterface
{
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return (is_numeric($value) || is_string($value)) && $this->isValid($this->convert($value));
    }

    /**
     * @inheritDoc
     */
    public function convert($value): string
    {
        return (string)$value;
    }

    private function isValid(string $value): bool
    {
        return (bool)preg_match($this->pattern, $value);
    }
}
