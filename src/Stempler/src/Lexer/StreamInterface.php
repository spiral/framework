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
    public function getOffset(): int;

    public function peak(): ?string;

    public function isEOI(): bool;
}
