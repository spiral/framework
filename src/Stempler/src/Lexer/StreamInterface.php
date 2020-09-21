<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

interface StreamInterface
{
    /**
     * @return int
     */
    public function getOffset(): int;

    /**
     * @return string|null
     */
    public function peak(): ?string;

    /**
     * @return bool
     */
    public function isEOI(): bool;
}
