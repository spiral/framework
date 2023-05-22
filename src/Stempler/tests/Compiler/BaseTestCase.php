<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Compiler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Compiler;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser;

abstract class BaseTestCase extends TestCase
{
    protected const GRAMMARS = [
        /* GRAMMAR => SYNTAX */
    ];

    protected const RENDERS = [
        /* RENDERER */
    ];

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

        return $compiler->compile($document)->getContent();
    }

    /**
     * @param string $string
     * @return Template
     */
    protected function parse(string $string): Template
    {
        $parser = new Parser();

        foreach (static::GRAMMARS as $grammar => $syntax) {
            $parser->addSyntax(new $grammar(), new $syntax());
        }

        return $parser->parse(new StringStream($string));
    }
}
