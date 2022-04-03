<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

final class Byte
{
    public function __construct(
        public int $offset,
        public string $char
    ) {
    }
}
