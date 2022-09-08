<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use Spiral\Stempler\Lexer\Lexer;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Lexer\Token;

class RawTest extends BaseTest
{
    protected const GRAMMARS = [];

    public function testRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body')
            ],
            ('raw body')
        );
    }

    protected function assertTokens(array $tokens, string $source): void
    {
        $parsed = $this->tokens($source);

        if (count($tokens) !== count($parsed)) {
            $this->fail('Token count mismatch');
        }

        foreach ($tokens as $index => $token) {
            $this->assertSame($token->type, $parsed[$index]->type, 'Token type mismatch');
            $this->assertSame($token->offset, $parsed[$index]->offset, 'Token offset mismatch');
            $this->assertSame($token->content, $parsed[$index]->content, 'Token content mismatch');
        }
    }

    protected function tokens(string $source): array
    {
        $lexer = new Lexer();

        $tokens = [];
        foreach ($lexer->parse(new StringStream($source)) as $t) {
            $tokens[] = $t;
        }

        return $tokens;
    }
}
