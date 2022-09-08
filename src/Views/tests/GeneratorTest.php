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

        $this->assertCount(1, $variants);
        $this->assertSame($context->getID(), $variants[0]->getID());
        $this->assertSame('value', $variants[0]->resolveValue('test'));
    }

    public function testRotateEmpty(): void
    {
        $context = new ViewContext();

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        $this->assertCount(0, $variants);
    }

    public function testRotateMultiValue(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('test', 'value', ['value', 'another']));

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        $this->assertCount(2, $variants);
        $this->assertSame($context->getID(), $variants[0]->getID());
        $this->assertSame('value', $variants[0]->resolveValue('test'));
        $this->assertSame('another', $variants[1]->resolveValue('test'));
    }

    public function testRotateMultiple(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('a', 'a', ['a', 'b']));
        $context = $context->withDependency(new ValueDependency('b', 'c', ['c', 'e']));
        $context = $context->withDependency(new ValueDependency('d', 'f', ['f', 'g']));

        $generator = new ContextGenerator($context);
        $variants = $generator->generate();

        $this->assertCount(8, $variants);
        $this->assertSame($context->getID(), $variants[0]->getID());

        // ending
        $this->assertSame('b', $variants[7]->resolveValue('a'));
        $this->assertSame('e', $variants[7]->resolveValue('b'));
        $this->assertSame('g', $variants[7]->resolveValue('d'));
    }
}
