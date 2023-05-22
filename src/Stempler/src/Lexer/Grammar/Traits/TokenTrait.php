<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar\Traits;

use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Token;

trait TokenTrait
{
    /** @var array<int<0, max>, Token> */
    private array $tokens = [];

    private function packToken(array $inner, int $type): Token
    {
        $token = new Token($type, 0, '');
        $token->offset = null;

        $buffer = null;
        $bufferOffset = 0;

        foreach ($inner as $n) {
            $token->offset ??= $n->offset;

            if ($n instanceof Byte) {
                if ($buffer === null) {
                    $buffer = '';
                    $bufferOffset = $n->offset;
                }

                $buffer .= $n->char;
                $token->content .= $n->char;

                continue;
            }

            if ($buffer !== null) {
                $token->tokens[] = new Token(
                    Token::TYPE_RAW,
                    $bufferOffset,
                    $buffer,
                    self::class
                );
                $buffer = null;
            }

            $token->content .= $n->content;
            $token->tokens[] = $n;
        }

        if ($buffer !== null) {
            $token->tokens[] = new Token(
                Token::TYPE_RAW,
                $bufferOffset,
                $buffer,
                self::class
            );
        }

        if (\count($token->tokens) === 1 && $token->tokens[0]->type === Token::TYPE_RAW) {
            $token->tokens = [];
        }

        return $token;
    }
}
