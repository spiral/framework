<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Debug;

use PHPUnit\Framework\TestCase;
use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\PlainRenderer;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Exceptions\ValueWrapper;

class ValueWrapperTest extends TestCase
{
    public function testInteger(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('100', implode(',', $wrapper->wrap([100])));
    }

    public function testString(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello world'])));
    }

    public function testArray(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('array', implode(',', $wrapper->wrap([['hello world']])));
    }

    public function testNull(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('null', implode(',', $wrapper->wrap([null])));
    }

    public function testBool(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('true', implode(',', $wrapper->wrap([true])));
        $this->assertStringContainsString('false', implode(',', $wrapper->wrap([false])));
    }

    public function testObject(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('Dumper', implode(',', $wrapper->wrap([new Dumper()])));
    }

    public function testDoNotAggregateValues(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), 0);

        $this->assertStringContainsString('100', implode(',', $wrapper->wrap([100])));
        $this->assertCount(0, $wrapper->getValues());
    }

    public function testAggregateValues(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello'])));
        $this->assertCount(1, $wrapper->getValues());
    }

    public function testAggregateMultipleValues(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello'])));
        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello'])));
        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello'])));
        $this->assertStringContainsString('string', implode(',', $wrapper->wrap(['hello'])));

        $this->assertCount(1, $wrapper->getValues());
    }

    public function testAggregateValuesInline(): void
    {
        $wrapper = new ValueWrapper(new Dumper(), new PlainRenderer(), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('100', implode(',', $wrapper->wrap([100])));
        $this->assertCount(0, $wrapper->getValues());
    }
}
