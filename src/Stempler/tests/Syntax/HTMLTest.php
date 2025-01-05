<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

class HTMLTest extends BaseTestCase
{
    protected const GRAMMARS = [
        HTMLGrammar::class => HTMLSyntax::class,
    ];

    public function testRaw(): void
    {
        $doc = $this->parse('raw');

        self::assertInstanceOf(Raw::class, $doc->nodes[0]);
        self::assertSame('raw', $doc->nodes[0]->content);
    }

    public function testNode(): void
    {
        $doc = $this->parse('<a href="google.com">hello world</a>');

        self::assertInstanceOf(Tag::class, $doc->nodes[0]);

        self::assertSame('a', $doc->nodes[0]->name);
        self::assertFalse($doc->nodes[0]->void);

        self::assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[0]);

        self::assertSame('href', $doc->nodes[0]->attrs[0]->name);
        self::assertSame('"google.com"', $doc->nodes[0]->attrs[0]->value);
        self::assertSame('hello world', $doc->nodes[0]->nodes[0]->content);
    }

    public function testShortNode(): void
    {
        $doc = $this->parse('<br>');

        self::assertInstanceOf(Tag::class, $doc->nodes[0]);

        self::assertSame('br', $doc->nodes[0]->name);
        self::assertTrue($doc->nodes[0]->void);
        self::assertSame([], $doc->nodes[0]->attrs);
        self::assertSame([], $doc->nodes[0]->nodes);
    }

    public function testShortNode2(): void
    {
        $doc = $this->parse('<embed url="google.com"/>');

        self::assertInstanceOf(Tag::class, $doc->nodes[0]);

        self::assertSame('embed', $doc->nodes[0]->name);
        self::assertTrue($doc->nodes[0]->void);

        self::assertSame('url', $doc->nodes[0]->attrs[0]->name);
        self::assertSame('"google.com"', $doc->nodes[0]->attrs[0]->value);

        self::assertSame([], $doc->nodes[0]->nodes);
    }

    public function testBrokenNode(): void
    {
        $this->expectException(ParserException::class);

        $this->parse('<a href="">');
    }

    public function testBrokenNested(): void
    {
        $this->expectException(ParserException::class);

        $this->parse('<a href=""></b>');
    }

    public function testVerbatimAttribute(): void
    {
        $doc = $this->parse('<a style="color: blue" onclick="alert(1);"></a>');

        self::assertSame('a', $doc->nodes[0]->name);
        self::assertFalse($doc->nodes[0]->void);

        self::assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[0]);
        self::assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[1]);

        self::assertSame('style', $doc->nodes[0]->attrs[0]->name);
        self::assertSame('onclick', $doc->nodes[0]->attrs[1]->name);

        self::assertInstanceOf(Verbatim::class, $doc->nodes[0]->attrs[0]->value);
        self::assertInstanceOf(Verbatim::class, $doc->nodes[0]->attrs[1]->value);
    }
}
