<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

use Spiral\Stempler\Exception\ScannerException;

final class StringStream implements StreamInterface
{
    /** @var string */
    private $source;

    /** @var int */
    private $length;

    /** @var int */
    private $offset;

    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = $source;
        $this->length = strlen($source);
        $this->offset = 0;
    }

    /**
     * Current scanner offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Peak next character and advance the position.
     *
     * @return string|null
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

    /**
     * @return bool
     */
    public function isEOI(): bool
    {
        return $this->offset >= $this->length;
    }
}
