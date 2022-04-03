<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar;

use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Token;

final class PHPGrammar implements GrammarInterface
{
    /** @var int */
    public const TYPE_CODE = 400;

    public function parse(Buffer $src): \Generator
    {
        while ($n = $src->next()) {
            if (!$n instanceof Byte || $n->char !== '<' || $src->lookaheadByte() !== '?') {
                yield $n;
                continue;
            }

            $php = $this->parseGrammar($n->char . $src->nextBytes(), $n->offset);
            if ($php === null) {
                yield $n;
                $src->replay($n->offset);
                continue;
            }

            yield $php;
            $src->replay($n->offset + \strlen($php->content) - 1);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public static function tokenName(int $token): string
    {
        return 'PHP:CODE';
    }

    private function parseGrammar(string $content, int $offset): ?Token
    {
        $tokens = null;
        foreach (\token_get_all($content) as $token) {
            if ($tokens === null && !$this->is($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO])) {
                // not php
                return null;
            }

            $tokens[] = $token;
            if ($this->is($token, [T_CLOSE_TAG])) {
                break;
            }
        }

        if ($tokens === null) {
            return null;
        }

        $buffer = '';
        foreach ($tokens as $token) {
            if (!\is_array($token)) {
                $buffer .= $token;
                continue;
            }
            $buffer .= $token[1];
        }

        $token = new Token(self::TYPE_CODE, $offset, $buffer);
        $token->tokens = $tokens;

        return $token;
    }

    private function is(array|string $token, array $type): bool
    {
        if (!\is_array($token)) {
            return false;
        }

        return \in_array($token[0], $type);
    }
}
