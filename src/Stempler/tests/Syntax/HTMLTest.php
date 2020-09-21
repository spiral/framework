<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

class HTMLTest extends BaseTest
{
    protected const GRAMMARS = [
        HTMLGrammar::class => HTMLSyntax::class,
    ];

    public function testRaw(): void
    {
        $doc = $this->parse('raw');

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]);
        $this->assertSame('raw', $doc->nodes[0]->content);
    }

    public function testNode(): void
    {
        $doc = $this->parse('<a href="google.com">hello world</a>');

        $this->assertInstanceOf(Tag::class, $doc->nodes[0]);

        $this->assertSame('a', $doc->nodes[0]->name);
        $this->assertFalse($doc->nodes[0]->void);

        $this->assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[0]);

        $this->assertSame('href', $doc->nodes[0]->attrs[0]->name);
        $this->assertSame('"google.com"', $doc->nodes[0]->attrs[0]->value);
        $this->assertSame('hello world', $doc->nodes[0]->nodes[0]->content);
    }

    public function testShortNode(): void
    {
        $doc = $this->parse('<br>');

        $this->assertInstanceOf(Tag::class, $doc->nodes[0]);

        $this->assertSame('br', $doc->nodes[0]->name);
        $this->assertTrue($doc->nodes[0]->void);
        $this->assertSame([], $doc->nodes[0]->attrs);
        $this->assertSame([], $doc->nodes[0]->nodes);
    }

    public function testShortNode2(): void
    {
        $doc = $this->parse('<embed url="google.com"/>');

        $this->assertInstanceOf(Tag::class, $doc->nodes[0]);

        $this->assertSame('embed', $doc->nodes[0]->name);
        $this->assertTrue($doc->nodes[0]->void);

        $this->assertSame('url', $doc->nodes[0]->attrs[0]->name);
        $this->assertSame('"google.com"', $doc->nodes[0]->attrs[0]->value);

        $this->assertSame([], $doc->nodes[0]->nodes);
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

        $this->assertSame('a', $doc->nodes[0]->name);
        $this->assertFalse($doc->nodes[0]->void);

        $this->assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[0]);
        $this->assertInstanceOf(Attr::class, $doc->nodes[0]->attrs[1]);

        $this->assertSame('style', $doc->nodes[0]->attrs[0]->name);
        $this->assertSame('onclick', $doc->nodes[0]->attrs[1]->name);

        $this->assertInstanceOf(Verbatim::class, $doc->nodes[0]->attrs[0]->value);
        $this->assertInstanceOf(Verbatim::class, $doc->nodes[0]->attrs[1]->value);
    }
}
