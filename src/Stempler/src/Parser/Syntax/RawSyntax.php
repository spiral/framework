<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax;

use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * Register simple text inclusions.
 */
final class RawSyntax implements SyntaxInterface
{
    public function handle(Parser $parser, Assembler $asm, Token $token): void
    {
        $asm->push(new Raw(
            $token->content,
            new Parser\Context($token, $parser->getPath())
        ));
    }
}
