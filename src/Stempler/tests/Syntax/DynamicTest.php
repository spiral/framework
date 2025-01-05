<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;

class DynamicTest extends BaseTestCase
{
    protected const GRAMMARS = [
        DynamicGrammar::class => DynamicSyntax::class,
    ];

    public function testRaw(): void
    {
        $doc = $this->parse('raw');

        self::assertInstanceOf(Raw::class, $doc->nodes[0]);
        self::assertSame('raw', $doc->nodes[0]->content);
    }

    public function testEmptyDirective(): void
    {
        $doc = $this->parse('@directive');

        self::assertInstanceOf(Directive::class, $doc->nodes[0]);
        self::assertSame('directive', $doc->nodes[0]->name);
        self::assertNull($doc->nodes[0]->body);
    }

    public function testDirectiveWithBody(): void
    {
        $doc = $this->parse('@directive(100, [])');

        self::assertInstanceOf(Directive::class, $doc->nodes[0]);
        self::assertSame('directive', $doc->nodes[0]->name);
        self::assertSame('100, []', $doc->nodes[0]->body);
    }

    public function testDeclareSkip(): void
    {
        $doc = $this->parse('@declare');
        self::assertCount(0, $doc->nodes);
    }

    public function testOutput(): void
    {
        $doc = $this->parse('{{ $name }}');

        self::assertInstanceOf(Output::class, $doc->nodes[0]);
        self::assertFalse($doc->nodes[0]->rawOutput);
        self::assertSame(' $name ', $doc->nodes[0]->body);
    }

    public function testRawOutput(): void
    {
        $doc = $this->parse('{!! $name !!}');

        self::assertInstanceOf(Output::class, $doc->nodes[0]);
        self::assertTrue($doc->nodes[0]->rawOutput);
        self::assertSame(' $name ', $doc->nodes[0]->body);
    }
}
