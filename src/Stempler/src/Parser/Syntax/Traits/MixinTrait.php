<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax\Traits;

use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;

trait MixinTrait
{
    private function parseToken(Parser $parser, Token $token): Mixin|Raw|string
    {
        if ($token->tokens === []) {
            if ($token->type === Token::TYPE_RAW) {
                return new Raw($token->content);
            }

            return $token->content;
        }

        $mixin = new Mixin([], new Parser\Context($token, $parser->getPath()));
        /**
         * TODO issue #767
         * @link https://github.com/spiral/framework/issues/767
         * @psalm-suppress InvalidArgument
         */
        $parser->parseTokens(
            new Assembler($mixin, 'nodes'),
            $token->tokens
        );

        return $mixin;
    }
}
