<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Options;

final class OptionsTest extends TestCase
{
    public function testQueue(): void
    {
        $options = new Options();

        $this->assertNull($options->getQueue());
        $this->assertSame('foo', $options->withQueue('foo')->getQueue());
    }

    public function testDelay(): void
    {
        $options = new Options();

        $this->assertNull($options->getDelay());
        $this->assertSame(1, $options->withDelay(1)->getDelay());
        $this->assertSame(0, $options->withDelay(0)->getDelay());
        $this->assertNull($options->withDelay(null)->getDelay());
    }

    public function testHeaders(): void
    {
        $options = new Options();

        $this->assertSame([], $options->getHeaders());
        $this->assertFalse($options->hasHeader('foo'));
        $this->assertSame([], $options->getHeader('foo'));
        $this->assertSame('', $options->getHeaderLine('foo'));

        $options = $options->withHeader('foo', ['bar', 'baz']);

        $this->assertSame(['foo' => ['bar', 'baz']], $options->getHeaders());
        $this->assertTrue($options->hasHeader('foo'));
        $this->assertSame(['bar', 'baz'], $options->getHeader('foo'));
        $this->assertSame('bar,baz', $options->getHeaderLine('foo'));
    }

    public function testWithAddedHeader(): void
    {
        $options = new Options();

        $this->assertSame(
            ['foo' => ['some', 'other']],
            $options->withHeader('foo', 'some')->withAddedHeader('foo', 'other')->getHeaders()
        );
    }

    public function testWithoutHeader(): void
    {
        $options = (new Options())->withHeader('foo', 'bar');

        $this->assertSame([], $options->withoutHeader('foo')->getHeaders());
    }

    public function testJsonSerialize(): void
    {
        $options = (new Options())
            ->withDelay(5)
            ->withQueue('foo')
            ->withHeader('foo', 'bar');

        $this->assertSame([
            'delay' => 5,
            'queue' => 'foo',
            'headers' => ['foo' => ['bar']],
        ], $options->jsonSerialize());
    }
}
