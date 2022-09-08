<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Directive\DirectiveGroup;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

abstract class BaseTest extends \Spiral\Tests\Stempler\Compiler\BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
        HTMLRenderer::class,
    ];

    protected const GRAMMARS = [
        DynamicGrammar::class => DynamicSyntax::class,
        HTMLGrammar::class    => HTMLSyntax::class
    ];

    protected const DIRECTIVES = [];

    /**
 * @param Template $document
 * @return string
 */
    protected function compile(Template $document): string
    {
        $compiler = new Compiler();
        foreach (static::RENDERS as $renderer) {
            $compiler->addRenderer(new $renderer());
        }

        $directiveGroup = new DirectiveGroup();
        foreach (static::DIRECTIVES as $directive) {
            $directiveGroup->addDirective(new $directive());
        }

        $compiler->addRenderer(new Compiler\Renderer\DynamicRenderer($directiveGroup));

        return $compiler->compile($document)->getContent();
    }
}
