<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

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
use Spiral\Stempler\Loader\DirectoryLoader;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;
use Spiral\Stempler\Parser\Syntax\InlineSyntax;
use Spiral\Stempler\Parser\Syntax\PHPSyntax;

abstract class BaseTest extends TestCase
{
    protected function compile(string $source, array $visitors = [], LoaderInterface $loader = null)
    {
        if ($loader === null) {
            $loader = new StringLoader();
            $loader->set('root', $source);
        }

        $builder = $this->getBuilder($loader, $visitors);

        return $builder->compile('root');
    }

    protected function parse(string $source, array $visitors = [], LoaderInterface $loader = null)
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', $source);

        $builder = $this->getBuilder($loader, $visitors);

        return $builder->load('root');
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
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

        foreach ($this->getVisitors() as $visitor) {
            $builder->addVisitor($visitor);
        }

        foreach ($visitors as $visitor) {
            $builder->addVisitor($visitor);
        }

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [];
    }

    /**
     * @return LoaderInterface
     */
    protected function getFixtureLoader(): LoaderInterface
    {
        return new DirectoryLoader(__DIR__ . '/../fixtures');
    }
}
