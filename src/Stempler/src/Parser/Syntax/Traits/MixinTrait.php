<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax\Traits;

use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;

trait MixinTrait
{
    /**
     * @return Mixin|string
     */
    private function parseToken(Parser $parser, Token $token)
    {
        if ($token->tokens === []) {
            if ($token->type === Token::TYPE_RAW) {
                return new Raw($token->content);
            }

            return $token->content;
        }

        $mixin = new Mixin([], new Parser\Context($token, $parser->getPath()));
        $parser->parseTokens(
            new Assembler($mixin, 'nodes'),
            $token->tokens
        );

        return $mixin;
    }
}
