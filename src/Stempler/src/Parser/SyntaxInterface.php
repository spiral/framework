<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Parser;

interface SyntaxInterface
{
    /**
     * @throws SyntaxException
     */
    public function handle(Parser $parser, Assembler $asm, Token $token): void;
}
