<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Lexer\Lexer;
use Spiral\Stempler\Lexer\StringStream;

abstract class BaseTest extends TestCase
{
    protected const GRAMMARS = [];

    /**
     * @param array  $tokens
     * @param string $source
     */
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
        foreach (static::GRAMMARS as $grammar) {
            $lexer->addGrammar(new $grammar());
        }

        $tokens = [];
        foreach ($lexer->parse(new StringStream($source)) as $t) {
            $tokens[] = $t;
        }

        return $tokens;
    }
}
