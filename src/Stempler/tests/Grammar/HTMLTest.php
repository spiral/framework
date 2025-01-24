<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Lexer\Token;

class HTMLTest extends BaseTestCase
{
    protected const GRAMMARS = [HTMLGrammar::class];

    public function testRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body'),
            ],
            ('raw body'),
        );
    }

    public function testTag(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 4, '>'),
            ],
            ('<tag>'),
        );
    }

    public function testTagOffset(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<'),
                new Token(HTMLGrammar::TYPE_OPEN, 1, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 2, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 5, '>'),
            ],
            ('<<tag>'),
        );
    }

    public function testTagCloseShort(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE_SHORT, 4, '/>'),
            ],
            ('<tag/>'),
        );
    }

    public function testTagAttribute(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'tag'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 4, ' '),
                new Token(HTMLGrammar::TYPE_KEYWORD, 5, 'param'),
                new Token(HTMLGrammar::TYPE_EQUAL, 10, '='),
                new Token(HTMLGrammar::TYPE_ATTRIBUTE, 11, '"value"'),
                new Token(HTMLGrammar::TYPE_CLOSE_SHORT, 18, '/>'),
            ],
            ('<tag param="value"/>'),
        );
    }

    public function testInvalidTag(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<tag param="value"'),
            ],
            ('<tag param="value"'),
        );
    }

    public function testInvalidTag2(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<tag param="value"/'),
            ],
            ('<tag param="value"/'),
        );
    }

    public function testInvalidTag3(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<tag param="value"<>'),
            ],
            ('<tag param="value"<>'),
        );
    }

    public function testInvalidTag4(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<#tag param="value">'),
            ],
            ('<#tag param="value">'),
        );
    }

    public function testInvalidTag5(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<tag param="value"'),
                new Token(HTMLGrammar::TYPE_OPEN, 18, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 19, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 22, '>'),
            ],
            ('<tag param="value"<tag>'),
        );
    }

    public function testInvalidTag6(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<tag param="value'),
            ],
            ('<tag param="value'),
        );
    }

    public function testInvalidTag7(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<>'),
            ],
            ('<>'),
        );
    }

    public function testInvalidTag8(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<"">'),
            ],
            ('<"">'),
        );
    }

    public function testInvalidTag9(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<=>'),
            ],
            ('<=>'),
        );
    }

    public function testInvalidTag10(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '< "=" keyword >'),
            ],
            ('< "=" keyword >'),
        );
    }

    public function testTagWhitespace(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 1, ' '),
                new Token(HTMLGrammar::TYPE_KEYWORD, 2, 'tag'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 5, ' '),
                new Token(HTMLGrammar::TYPE_CLOSE, 6, '>'),
            ],
            ('< tag >'),
        );
    }

    public function testDoubleWhitespace(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 1, '  '),
                new Token(HTMLGrammar::TYPE_KEYWORD, 3, 'tag'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 6, '  '),
                new Token(HTMLGrammar::TYPE_CLOSE, 8, '>'),
            ],
            ('<  tag  >'),
        );
    }

    public function testTagOpenShort(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 0, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 2, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 5, '>'),
            ],
            ('</tag>'),
        );
    }

    public function testTagWithBody(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 4, '>'),
                new Token(Token::TYPE_RAW, 5, 'body'),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 9, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 11, 'tag'),
                new Token(HTMLGrammar::TYPE_CLOSE, 14, '>'),
            ],
            ('<tag>body</tag>'),
        );
    }

    public function testScript(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, 'alert("<a>");'),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 21, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 23, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 29, '>'),
            ],
            ('<script>alert("<a>");</script>'),
        );
    }

    public function testCloseScript(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, 'alert("</script>");'),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 27, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 29, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 35, '>'),
            ],
            ('<script>alert("</script>");</script>'),
        );
    }

    public function testCommentScript(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, 'alert("</script>"); //hello '),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 36, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 38, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 44, '>'),
            ],
            ('<script>alert("</script>"); //hello </script>'),
        );
    }

    public function testCommentScriptQuotes(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, 'alert("</script>"); //hello"'),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 36, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 38, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 44, '>'),
            ],
            ('<script>alert("</script>"); //hello"</script>'),
        );
    }

    public function testCommentAlternative(): void
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, "alert(\"</script>\"); /*hello\n'\"*/"),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 40, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 42, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 48, '>'),
            ],
            ("<script>alert(\"</script>\"); /*hello\n'\"*/</script>"),
        );
    }
}
