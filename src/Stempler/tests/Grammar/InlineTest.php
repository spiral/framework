<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use Spiral\Stempler\Lexer\Grammar\InlineGrammar;
use Spiral\Stempler\Lexer\Token;

class InlineTest extends BaseTestCase
{
    protected const GRAMMARS = [InlineGrammar::class];

    public function testRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body')
            ],
            ('raw body')
        );
    }

    public function testInject(): void
    {
        $this->assertTokens(
            [
                new Token(InlineGrammar::TYPE_OPEN_TAG, 0, '${'),
                new Token(InlineGrammar::TYPE_NAME, 2, 'name'),
                new Token(InlineGrammar::TYPE_CLOSE_TAG, 6, '}'),
            ],
            ('${name}')
        );
    }

    public function testInjectMultiline(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(InlineGrammar::TYPE_OPEN_TAG, 0, '${'),
                new Token(InlineGrammar::TYPE_NAME, 3, 'name'),
                new Token(InlineGrammar::TYPE_CLOSE_TAG, 8, '}'),
            ],
            ('${ name }')
        );
    }

    public function testDefault(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(InlineGrammar::TYPE_OPEN_TAG, 0, '${'),
                new Token(InlineGrammar::TYPE_NAME, 2, 'name'),
                new Token(InlineGrammar::TYPE_SEPARATOR, 6, '|'),
                new Token(InlineGrammar::TYPE_DEFAULT, 7, 'default'),
                new Token(InlineGrammar::TYPE_CLOSE_TAG, 14, '}'),
            ],
            ('${name|default}')
        );
    }

    public function testDefaultQuotes(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(InlineGrammar::TYPE_OPEN_TAG, 0, '${'),
                new Token(InlineGrammar::TYPE_NAME, 2, 'name'),
                new Token(InlineGrammar::TYPE_SEPARATOR, 6, '|'),
                new Token(InlineGrammar::TYPE_DEFAULT, 7, '"default"'),
                new Token(InlineGrammar::TYPE_CLOSE_TAG, 16, '}'),
            ],
            ('${name|"default"}')
        );
    }

    public function testInvalid(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '${}'),
            ],
            ('${}')
        );
    }

    public function testInvalid2(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '${|}'),
            ],
            ('${|}')
        );
    }

    public function testInvalid3(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '${name|}'),
            ],
            ('${name|}')
        );
    }

    public function testInvalid4(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '${|default}'),
            ],
            ('${|default}')
        );
    }

    public function testInvalid5(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '${name|default|default2}'),
            ],
            ('${name|default|default2}')
        );
    }

    public function testDefaultSpaces(): void
    {
        // no whitespaces
        $this->assertTokens(
            [
                new Token(InlineGrammar::TYPE_OPEN_TAG, 0, '${'),
                new Token(InlineGrammar::TYPE_NAME, 2, 'name'),
                new Token(InlineGrammar::TYPE_SEPARATOR, 6, '|'),
                new Token(InlineGrammar::TYPE_DEFAULT, 7, ' default '),
                new Token(InlineGrammar::TYPE_CLOSE_TAG, 16, '}'),
            ],
            ('${name| default }')
        );
    }
}
