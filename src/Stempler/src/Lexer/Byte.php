<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

final class Byte
{
    /** @var int */
    public $offset;

    /** @var string */
    public $char;

    public function __construct(int $offset, string $char)
    {
        $this->offset = $offset;
        $this->char = $char;
    }
}
