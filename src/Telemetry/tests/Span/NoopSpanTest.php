<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Span;

use PHPUnit\Framework\TestCase;
use Spiral\Telemetry\Span\NoopSpan;

final class NoopSpanTest extends TestCase
{
    public function testKeepsNothing(): void
    {
        $span = NoopSpan::instance();

        self::assertSame($span, $span->updateName('foo'));
        self::assertSame($span, $span->setStatus('error', 'bar'));
        self::assertSame($span, $span->setAttributes(['foo' => 'bar']));
        self::assertSame($span, $span->setAttribute('baz', 1));

        self::assertSame('noop', $span->getName());
        self::assertNull($span->getStatus());
        self::assertSame([], $span->getAttributes());
        self::assertFalse($span->hasAttribute('foo'));
        self::assertNull($span->getAttribute('foo'));
    }

    public function testIsSingleton(): void
    {
        self::assertSame(NoopSpan::instance(), NoopSpan::instance());
    }
}
