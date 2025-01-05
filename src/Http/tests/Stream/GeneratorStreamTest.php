<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Stream;

use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spiral\Http\Stream\GeneratorStream;

final class GeneratorStreamTest extends TestCase
{
    protected const DEFAULT_SEQUENCE = [0, 'foo', 1, 'bar', 42, 'baz', '', "\n", 'end'];
    protected const DEFAULT_CONTENT_RESULT = "0foo1bar42baz\nend";

    public function testGetSize(): void
    {
        $stream = $this->createStream();

        self::assertNull($stream->getSize());
    }

    public function testIsSeekable(): void
    {
        $stream = $this->createStream();

        self::assertFalse($stream->isSeekable());
    }

    public function testSeek(): void
    {
        $stream = $this->createStream();

        $this->expectException(RuntimeException::class);

        $stream->seek(5);
    }

    public function testRewindOnInit(): void
    {
        $stream = $this->createStream();

        $stream->rewind();

        self::assertSame(0, $stream->tell());
    }

    public function testRewindAfterRead(): void
    {
        $stream = $this->createStream();
        $stream->read(1);
        $stream->read(1);

        $this->expectException(\Exception::class);

        $stream->rewind();
    }

    public function testIsWritable(): void
    {
        $stream = $this->createStream();

        self::assertFalse($stream->isWritable());
    }

    public function testWrite(): void
    {
        $stream = $this->createStream();

        $this->expectException(RuntimeException::class);

        $stream->write('test');
    }

    public function testRead(): void
    {
        $stream = $this->createStream();

        $result1 = $stream->read(4);
        $result2 = $stream->read(4);

        self::assertSame('0', $result1);
        self::assertSame('foo', $result2);
    }

    public function testReadWithReturnOnly(): void
    {
        $rValue = 'return-value';
        $stream = $this->createStream([], $rValue);

        $result = $stream->read(12);

        self::assertSame($rValue, $result);
    }

    public function testToStringWithReturn(): void
    {
        $rValue = 'return-value';
        $stream = $this->createStream(self::DEFAULT_SEQUENCE, $rValue);

        $result = (string) $stream;

        self::assertSame(self::DEFAULT_CONTENT_RESULT . $rValue, $result);
    }

    public function testToStringWithReturnOnly(): void
    {
        $rValue = 'return-value';
        $stream = $this->createStream([], $rValue);

        $result = (string) $stream;

        self::assertSame($rValue, $result);
    }

    public function testUnableReadStream(): void
    {
        $stream = $this->createStream();
        $stream->close();

        self::assertSame('', (string) $stream);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read stream contents.');

        $stream->getContents();
    }

    public function testClose(): void
    {
        $stream = $this->createStream();
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read from non-readable stream.');

        $stream->read(1);

        self::assertFalse($stream->isReadable());
        self::assertNull($stream->getSize());
        self::assertSame(0, $stream->tell());
    }

    public function testEof(): void
    {
        $stream = $this->createStream();

        self::assertFalse($stream->eof());

        $stream->close();

        self::assertTrue($stream->eof());
    }

    public function testIsReadable(): void
    {
        $stream = $this->createStream();

        self::assertTrue($stream->isReadable());

        $stream->close();

        self::assertFalse($stream->isReadable());
    }

    public function testGetMetadata(): void
    {
        $stream = $this->createStream();

        self::assertSame(['seekable' => false, 'eof' => false], $stream->getMetadata());
    }

    private function createStream(iterable $sequence = self::DEFAULT_SEQUENCE, $return = null): GeneratorStream
    {
        $function = static function (iterable $iterable, $return): Generator {
            yield from $iterable;
            return $return;
        };
        return new GeneratorStream($function($sequence, $return));
    }
}
