<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Syntax;

use Spiral\Stempler\Lexer\StringStream;
use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Lexer;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser;

abstract class BaseTestCase extends TestCase
{
    protected const GRAMMARS = [
        /* GRAMMAR => SYNTAX */
    ];

    protected function parse(string $string): Template
    {
        $parser = new Parser();

        foreach (static::GRAMMARS as $grammar => $syntax) {
            $parser->addSyntax(new $grammar(), new $syntax());
        }

        return $parser->parse(new StringStream($string));
    }
}
