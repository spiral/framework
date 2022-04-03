<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

use Spiral\Stempler\Exception\ScannerException;

final class StringStream implements StreamInterface
{
    private readonly int $length;
    private int $offset;

    public function __construct(
        private readonly string $source
    ) {
        $this->length = \strlen($source);
        $this->offset = 0;
    }

    /**
     * Current scanner offset.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Peak next character and advance the position.
     *
     * @throws ScannerException
     */
    public function peak(): ?string
    {
        if ($this->offset + 1 > $this->length) {
            return null;
        }

        return $this->source[$this->offset++];
    }

    public function isEOI(): bool
    {
        return $this->offset >= $this->length;
    }
}
