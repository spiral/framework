<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Compiler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

class HTMLTest extends BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
        HTMLRenderer::class,
    ];

    protected const GRAMMARS = [
        HTMLGrammar::class => HTMLSyntax::class,
    ];

    public function testCompileRaw(): void
    {
        $doc = $this->parse('<a href="google.com">click me</a>');

        $this->assertSame('<a href="google.com">click me</a>', $this->compile($doc));
    }

    public function testCompileNested(): void
    {
        $doc = $this->parse('<a href="google.com"><b>click me</b></a>');

        $this->assertSame('<a href="google.com"><b>click me</b></a>', $this->compile($doc));
    }

    public function testCompileNestedSingleQuote(): void
    {
        $doc = $this->parse('<a href=\'google.com\'><b>click me</b></a>');

        $this->assertSame('<a href=\'google.com\'><b>click me</b></a>', $this->compile($doc));
    }

    public function testCompileVoid(): void
    {
        $doc = $this->parse('<br>');

        $this->assertSame('<br/>', $this->compile($doc));
    }

    public function testCompileScript(): void
    {
        $doc = $this->parse('<script>alert("hello <b>name</b>");</script>');

        $this->assertSame('<script>alert("hello <b>name</b>");</script>', $this->compile($doc));
    }
}
