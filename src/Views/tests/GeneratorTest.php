<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\ContextGenerator;
use Spiral\Views\ViewContext;

class GeneratorTest extends TestCase
{
    public function testRotateSingleValue(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('test', 'value'));

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        self::assertCount(1, $variants);
        self::assertSame($context->getID(), $variants[0]->getID());
        self::assertSame('value', $variants[0]->resolveValue('test'));
    }

    public function testRotateEmpty(): void
    {
        $context = new ViewContext();

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        self::assertCount(0, $variants);
    }

    public function testRotateMultiValue(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('test', 'value', ['value', 'another']));

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        self::assertCount(2, $variants);
        self::assertSame($context->getID(), $variants[0]->getID());
        self::assertSame('value', $variants[0]->resolveValue('test'));
        self::assertSame('another', $variants[1]->resolveValue('test'));
    }

    public function testRotateMultiple(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('a', 'a', ['a', 'b']));
        $context = $context->withDependency(new ValueDependency('b', 'c', ['c', 'e']));
        $context = $context->withDependency(new ValueDependency('d', 'f', ['f', 'g']));

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        self::assertCount(8, $variants);
        self::assertSame($context->getID(), $variants[0]->getID());

        // ending
        self::assertSame('b', $variants[7]->resolveValue('a'));
        self::assertSame('e', $variants[7]->resolveValue('b'));
        self::assertSame('g', $variants[7]->resolveValue('d'));
    }
}
