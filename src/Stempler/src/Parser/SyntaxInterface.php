<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Parser;

interface SyntaxInterface
{
    /**
     *
     * @param Parser    $parser
     * @param Assembler $asm
     * @param Token     $token
     *
     * @throws SyntaxException
     */
    public function handle(Parser $parser, Assembler $asm, Token $token): void;
}
