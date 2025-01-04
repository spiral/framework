<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use Spiral\Stempler\Lexer\Grammar\Dynamic\DeclareGrammar;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Token;

class DynamicTest extends BaseTestCase
{
    protected const GRAMMARS = [DynamicGrammar::class];

    public function testRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body')
            ],
            ('raw body')
        );
    }

    public function testEcho(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $var '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 8, '}}')
            ],
            ('{{ $var }}')
        );
    }

    public function testEchoWithString(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $var . "{{ hello world }}" '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 30, '}}')
            ],
            ('{{ $var . "{{ hello world }}" }}')
        );
    }

    public function testEchoRaw(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_RAW_TAG, 0, '{!!'),
                new Token(DynamicGrammar::TYPE_BODY, 3, ' $var '),
                new Token(DynamicGrammar::TYPE_CLOSE_RAW_TAG, 9, '!!}')
            ],
            ('{!! $var !!}')
        );
    }

    public function testInvalidEcho(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '{! $var }}'),
            ],
            ('{! $var }}')
        );
    }

    public function testInvalidEcho2(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '{{ $var !}'),
            ],
            ('{{ $var !}')
        );
    }

    public function testEscapedEcho(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 1, '{{ $var }}'),
            ],
            ('@{{ $var }}')
        );
    }

    public function testEscapedRawEcho(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 1, '{!! $var !!}'),
            ],
            ('@{!! $var !!}')
        );
    }

    public function testDirective(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
            ],
            ('@do')
        );
    }

    public function testDirectiveAfterRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, ' '),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 1, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 2, 'do'),
            ],
            (' @do')
        );
    }

    public function testDirectiveBeforeRaw(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(Token::TYPE_RAW, 3, ' '),
            ],
            ('@do ')
        );
    }

    public function testDirectiveBeforeRawAndValue(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(Token::TYPE_RAW, 3, ' ok'),
            ],
            ('@do ok')
        );
    }

    public function testDirectiveEmbedded(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '"'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 1, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 2, 'do'),
                new Token(Token::TYPE_RAW, 4, '"'),
            ],
            ('"@do"')
        );
    }

    public function testDirectiveAfterDirective(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 3, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 4, 'other'),
            ],
            ('@do@other')
        );
    }

    public function testDirectiveWithBody(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 11, ')'),
            ],
            ('@do(var=foo)')
        );
    }

    public function testDirectiveWithBodyConsecutive(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 11, ')'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 12, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 13, 'other'),
            ],
            ('@do(var=foo)@other')
        );
    }

    public function testDirectiveWithNestedParenthesis(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=(foo+(1))'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 17, ')'),
            ],
            ('@do(var=(foo+(1)))')
        );
    }

    public function testDirectiveWhitespaceBeforeBody(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_WHITESPACE, 3, ' '),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 4, '('),
                new Token(DynamicGrammar::TYPE_BODY, 5, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 12, ')'),
            ],
            ('@do (var=foo)')
        );
    }

    public function testDirectiveMultipleWhitespaceBeforeBody(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_WHITESPACE, 3, '  '),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 5, '('),
                new Token(DynamicGrammar::TYPE_BODY, 6, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 13, ')'),
            ],
            ('@do  (var=foo)')
        );
    }

    public function testDirectiveWithQuoteInBody(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var="(foo"'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 14, ')'),
                new Token(Token::TYPE_RAW, 15, ')'),
            ],
            ('@do(var="(foo"))')
        );
    }

    public function testInvalidDirective(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '@do(var=abc'),
            ],
            ('@do(var=abc')
        );
    }

    public function testDeclareDirective(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 8, ' ok'),
            ],
            ('@declare ok')
        );
    }

    public function testDeclareWithBodyDirective(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 15, ' ok'),
            ],
            ('@declare(hello) ok')
        );
    }

    public function testDeclareSyntaxOff(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 9, '}}'),
                new Token(Token::TYPE_RAW, 37, '{{ $name }}')
            ],
            ('{{ $name }}@declare( syntax = "off" ){{ $name }}')
        );
    }

    public function testDeclareSyntaxOn(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 20, '{{ $name }}'),
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 50, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 52, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 59, '}}'),
            ],
            ('@declare(syntax=off){{ $name }}@declare(syntax=on){{ $name }}')
        );
    }

    public function testDeclareCustomSyntax(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 31, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 33, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 40, '%}'),
            ],
            ('@declare(open="{%", close="%}"){% $name %}')
        );
    }

    public function testDeclareRawCustomSyntax(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_RAW_TAG, 37, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 39, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_RAW_TAG, 46, '%}'),
            ],
            ('@declare(openRaw="{%", closeRaw="%}"){% $name %}')
        );
    }

    public function testDeclareCustomDefault(): void
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 31, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 33, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 40, '%}'),
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 68, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 70, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 77, '}}'),
            ],
            ('@declare(open="{%", close="%}"){% $name %}@declare(syntax="default"){{ $name }}')
        );
    }

    public function testTokenName(): void
    {
        self::assertSame('DECLARE:KEYWORD', DeclareGrammar::tokenName(DeclareGrammar::TYPE_KEYWORD));
        self::assertSame('DECLARE:EQUAL', DeclareGrammar::tokenName(DeclareGrammar::TYPE_EQUAL));
        self::assertSame('DECLARE:COMMA', DeclareGrammar::tokenName(DeclareGrammar::TYPE_COMMA));
        self::assertSame('DECLARE:QUOTED', DeclareGrammar::tokenName(DeclareGrammar::TYPE_QUOTED));
    }
}
