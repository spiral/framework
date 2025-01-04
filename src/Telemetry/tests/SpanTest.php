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

        self::assertSame('foo', $span->getName());

        $span->updateName('bar');
        self::assertSame('bar', $span->getName());
    }

    public function testConstructorWithoutAttributes(): void
    {
        $span = new Span('foo');
        self::assertSame([], $span->getAttributes());
    }

    public function testConstructorWithAttributes(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        self::assertSame(['baz' => 'bar'], $span->getAttributes());
    }

    public function testSetsAttributes(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        $span->setAttribute('baf', 123);

        self::assertSame(['baz' => 'bar', 'baf' => 123], $span->getAttributes());

        $span->setAttributes(['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $span->getAttributes());
    }

    public function testHasAttribute(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);

        self::assertTrue($span->hasAttribute('baz'));
        self::assertFalse($span->hasAttribute('foo'));
    }

    public function testGetsAttribute(): void
    {
        $span = new Span('foo', ['baz' => 'bar']);
        self::assertSame('bar', $span->getAttribute('baz'));
        self::assertNull($span->getAttribute('baf'));
    }

    public function testSetsStatus(): void
    {
        $span = new Span('foo');

        self::assertNull($span->getStatus());

        $span->setStatus(404, 'Not found');

        self::assertSame(404, $span->getStatus()->code);
        self::assertSame('Not found', $span->getStatus()->description);
    }
}
