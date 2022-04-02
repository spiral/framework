<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\DeclarationInterface;

class Directives implements DeclarationInterface
{
    /** @var string[] */
    private array $directives;

    public function __construct(string ...$directives)
    {
        $this->directives = $directives;
    }

    public function render(int $indentLevel = 0): string
    {
        if (empty($this->directives)) {
            return '';
        }

        return 'declare(' . \implode(', ', $this->directives) . ');';
    }
}
