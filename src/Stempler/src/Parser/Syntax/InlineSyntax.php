<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax;

use Spiral\Stempler\Lexer\Grammar\InlineGrammar;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\Inline;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\SyntaxInterface;

final class InlineSyntax implements SyntaxInterface
{
    use Parser\Syntax\Traits\MixinTrait;

    private ?Inline $inline = null;

    public function handle(Parser $parser, Assembler $asm, Token $token): void
    {
        switch ($token->type) {
            case InlineGrammar::TYPE_OPEN_TAG:
                $this->inline = new Inline(new Parser\Context($token, $parser->getPath()));
                $asm->push($this->inline);
                break;

            case InlineGrammar::TYPE_NAME:
                $this->inline->name = $this->parseToken($parser, $token);
                break;

            case InlineGrammar::TYPE_DEFAULT:
                $this->inline->value = $this->parseToken($parser, $token);
                break;

            case InlineGrammar::TYPE_CLOSE_TAG:
                $this->inline = null;
                break;
        }
    }
}
