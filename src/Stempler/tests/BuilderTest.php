<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\DynamicRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Compiler\Renderer\PHPRenderer;
use Spiral\Stempler\Directive\DirectiveGroup;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Lexer\Grammar\InlineGrammar;
use Spiral\Stempler\Lexer\Grammar\PHPGrammar;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;
use Spiral\Stempler\Parser\Syntax\InlineSyntax;
use Spiral\Stempler\Parser\Syntax\PHPSyntax;

class BuilderTest extends TestCase
{
    public function testRaw(): void
    {
        $builder = $this->getBuilder(new StringLoader());
        $builder->getLoader()->set('home', 'hello world');

        self::assertSame('hello world', $builder->compile('home')->getContent());
    }

    public function testInvalidPath(): void
    {
        $this->expectException(\Spiral\Stempler\Exception\LoaderException::class);
        $builder = $this->getBuilder(new StringLoader());
        $builder->compile('missing');
    }

    protected function getBuilder(LoaderInterface $loader): Builder
    {
        $builder = new Builder($loader);

        // Grammars
        $builder->getParser()->addSyntax(new PHPGrammar(), new PHPSyntax());
        $builder->getParser()->addSyntax(new InlineGrammar(), new InlineSyntax());
        $builder->getParser()->addSyntax(new DynamicGrammar(), new DynamicSyntax());
        $builder->getParser()->addSyntax(new HTMLGrammar(), new HTMLSyntax());

        $builder->getCompiler()->addRenderer(new CoreRenderer());
        $builder->getCompiler()->addRenderer(new PHPRenderer());
        $builder->getCompiler()->addRenderer(new DynamicRenderer(new DirectiveGroup()));
        $builder->getCompiler()->addRenderer(new HTMLRenderer());

        return $builder;
    }
}
