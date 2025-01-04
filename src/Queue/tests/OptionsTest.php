<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Options;

final class OptionsTest extends TestCase
{
    public function testQueue(): void
    {
        $options = new Options();

        self::assertNull($options->getQueue());
        self::assertSame('foo', $options->withQueue('foo')->getQueue());
    }

    public function testDelay(): void
    {
        $options = new Options();

        self::assertNull($options->getDelay());
        self::assertSame(1, $options->withDelay(1)->getDelay());
        self::assertSame(0, $options->withDelay(0)->getDelay());
        self::assertNull($options->withDelay(null)->getDelay());
    }

    public function testHeaders(): void
    {
        $options = new Options();

        self::assertSame([], $options->getHeaders());
        self::assertFalse($options->hasHeader('foo'));
        self::assertSame([], $options->getHeader('foo'));
        self::assertSame('', $options->getHeaderLine('foo'));

        $options = $options->withHeader('foo', ['bar', 'baz']);

        self::assertSame(['foo' => ['bar', 'baz']], $options->getHeaders());
        self::assertTrue($options->hasHeader('foo'));
        self::assertSame(['bar', 'baz'], $options->getHeader('foo'));
        self::assertSame('bar,baz', $options->getHeaderLine('foo'));
    }

    public function testWithAddedHeader(): void
    {
        $options = new Options();

        self::assertSame(['foo' => ['some', 'other']], $options->withHeader('foo', 'some')->withAddedHeader('foo', 'other')->getHeaders());
    }

    public function testWithoutHeader(): void
    {
        $options = (new Options())->withHeader('foo', 'bar');

        self::assertSame([], $options->withoutHeader('foo')->getHeaders());
    }

    public function testJsonSerialize(): void
    {
        $options = (new Options())
            ->withDelay(5)
            ->withQueue('foo')
            ->withHeader('foo', 'bar');

        self::assertSame([
            'delay' => 5,
            'queue' => 'foo',
            'headers' => ['foo' => ['bar']],
        ], $options->jsonSerialize());
    }
}
