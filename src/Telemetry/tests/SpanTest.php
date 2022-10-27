<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use PHPUnit\Framework\TestCase;
use Spiral\Telemetry\Span;

final class SpanTest extends TestCase
{
    public function testName(): void
    {
        $span = new Span('foo');

        $this->assertSame('foo', $span->getName());

        $span->updateName('bar');
        $this->assertSame('bar', $span->getName());
    }

    public function testConstructorWithoutAttributes(): void
    {
        $span = new Span('foo');
        $this->assertSame([], $span->getAttributes());
    }

    public function testConstructorWithAttributes(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        $this->assertSame(['baz' => 'bar'], $span->getAttributes());
    }

    public function testSetsAttributes(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        $span->setAttribute('baf', 123);

        $this->assertSame(['baz' => 'bar', 'baf' => 123], $span->getAttributes());

        $span->setAttributes(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $span->getAttributes());
    }

    public function testHasAttribute(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);

        $this->assertTrue($span->hasAttribute('baz'));
        $this->assertFalse($span->hasAttribute('foo'));
    }

    public function testGetsAttribute(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        $this->assertSame('bar', $span->getAttribute('baz'));
        $this->assertNull($span->getAttribute('baf'));
    }

    public function testSetsStatus(): void
    {
        $span = new Span('foo');

        $this->assertNull($span->getStatus());

        $span->setStatus(404, 'Not found');

        $this->assertSame(404, $span->getStatus()->code);
        $this->assertSame('Not found', $span->getStatus()->description);
    }
}
