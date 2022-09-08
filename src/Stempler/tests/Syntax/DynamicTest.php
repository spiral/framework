<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;

class DynamicTest extends BaseTest
{
    protected const GRAMMARS = [
        DynamicGrammar::class => DynamicSyntax::class,
    ];

    public function testRaw(): void
    {
        $doc = $this->parse('raw');

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]);
        $this->assertSame('raw', $doc->nodes[0]->content);
    }

    public function testEmptyDirective(): void
    {
        $doc = $this->parse('@directive');

        $this->assertInstanceOf(Directive::class, $doc->nodes[0]);
        $this->assertSame('directive', $doc->nodes[0]->name);
        $this->assertSame(null, $doc->nodes[0]->body);
    }

    public function testDirectiveWithBody(): void
    {
        $doc = $this->parse('@directive(100, [])');

        $this->assertInstanceOf(Directive::class, $doc->nodes[0]);
        $this->assertSame('directive', $doc->nodes[0]->name);
        $this->assertSame('100, []', $doc->nodes[0]->body);
    }

    public function testDeclareSkip(): void
    {
        $doc = $this->parse('@declare');
        $this->assertCount(0, $doc->nodes);
    }

    public function testOutput(): void
    {
        $doc = $this->parse('{{ $name }}');

        $this->assertInstanceOf(Output::class, $doc->nodes[0]);
        $this->assertSame(false, $doc->nodes[0]->rawOutput);
        $this->assertSame(' $name ', $doc->nodes[0]->body);
    }

    public function testRawOutput(): void
    {
        $doc = $this->parse('{!! $name !!}');

        $this->assertInstanceOf(Output::class, $doc->nodes[0]);
        $this->assertSame(true, $doc->nodes[0]->rawOutput);
        $this->assertSame(' $name ', $doc->nodes[0]->body);
    }
}
