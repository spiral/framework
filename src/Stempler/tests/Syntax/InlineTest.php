<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Lexer\Grammar\InlineGrammar;
use Spiral\Stempler\Node\Inline;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\InlineSyntax;

class InlineTest extends BaseTest
{
    protected const GRAMMARS = [
        InlineGrammar::class => InlineSyntax::class,
    ];

    public function testRaw(): void
    {
        $doc = $this->parse('raw');

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]);
        $this->assertSame('raw', $doc->nodes[0]->content);
    }

    public function testInline(): void
    {
        $doc = $this->parse('${name}');

        $this->assertInstanceOf(Inline::class, $doc->nodes[0]);
        $this->assertSame('name', $doc->nodes[0]->name);
        $this->assertSame(null, $doc->nodes[0]->value);
    }

    public function testInlineDefault(): void
    {
        $doc = $this->parse('${name|default}');

        $this->assertInstanceOf(Inline::class, $doc->nodes[0]);
        $this->assertSame('name', $doc->nodes[0]->name);
        $this->assertSame('default', $doc->nodes[0]->value);
    }
}
