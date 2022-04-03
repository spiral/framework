<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax;

use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * Registers PHP blocks.
 */
final class PHPSyntax implements SyntaxInterface
{
    public function handle(Parser $parser, Assembler $asm, Token $token): void
    {
        $asm->push(new PHP(
            $token->content,
            $token->tokens,
            new Parser\Context($token, $parser->getPath())
        ));
    }
}
