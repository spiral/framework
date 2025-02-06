<?php

declare(strict_types=1);

namespace Spiral\Tests\Streams;

use PHPUnit\Framework\TestCase;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Streams\StreamWrapper;
use Nyholm\Psr7\Stream;

class StreamsTest extends TestCase
{
    private const FIXTURE_DIRECTORY = __DIR__ . '/fixtures';

    public function testGetUri(): void
    {
        $stream = Stream::create();
        $stream->write('sample text');

        $filename = StreamWrapper::getFilename($stream);

        self::assertFileExists($filename);
        self::assertSame(\strlen('sample text'), \filesize($filename));
        self::assertSame(\md5('sample text'), \md5_file($filename));

        $newFilename = self::FIXTURE_DIRECTORY . '/test.txt';
        \copy($filename, $newFilename);

        self::assertFileExists($newFilename);
        self::assertSame(\strlen('sample text'), \filesize($newFilename));
        self::assertSame(\md5('sample text'), \md5_file($newFilename));

        //Rewinding
        self::assertFileExists($newFilename);
        self::assertSame(\strlen('sample text'), \filesize($newFilename));
        self::assertSame(\md5('sample text'), \md5_file($newFilename));

        self::assertTrue(StreamWrapper::has($filename));
        self::assertFalse(StreamWrapper::has($newFilename));
    }

    public function testGetResource(): void
    {
        $stream = Stream::create();
        $stream->write('sample text');

        self::assertFalse(StreamWrapper::has($stream));
        $resource = StreamWrapper::getResource($stream);
        self::assertTrue(StreamWrapper::has($stream));

        self::assertIsResource($resource);
        self::assertSame('sample text', \stream_get_contents($resource, -1, 0));

        //Rewinding
        self::assertSame('sample text', \stream_get_contents($resource, -1, 0));

        \fseek($resource, 7);
        self::assertSame('text', \stream_get_contents($resource, -1));
        self::assertSame('sample', \stream_get_contents($resource, 6, 0));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('< 8.0')]
    public function testException(): void
    {
        try {
            \fopen('spiral://non-exists', 'rb');
        } catch (\Throwable $e) {
            self::assertStringContainsString('failed to open stream', $e->getMessage());
        }

        try {
            \filemtime('spiral://non-exists');
        } catch (\Throwable $e) {
            self::assertStringContainsString('stat failed', $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('>= 8.0')]
    public function testExceptionPHP8(): void
    {
        try {
            \fopen('spiral://non-exists', 'rb');
        } catch (\Throwable $e) {
            self::assertStringContainsString('Failed to open stream', $e->getMessage());
        }

        try {
            \filemtime('spiral://non-exists');
        } catch (\Throwable $e) {
            self::assertStringContainsString('stat failed', $e->getMessage());
        }
    }

    public function testWriteIntoStream(): void
    {
        $stream = Stream::create(\fopen('php://temp', 'wrb+'));
        $file = StreamWrapper::getFilename($stream);

        \file_put_contents($file, 'test');

        self::assertSame('test', \file_get_contents($file));

        StreamWrapper::release($file);
    }

    protected function setUp(): void
    {
        $files = new Files();
        $files->ensureDirectory(self::FIXTURE_DIRECTORY, FilesInterface::RUNTIME);
    }

    protected function tearDown(): void
    {
        $files = new Files();
        $files->deleteDirectory(self::FIXTURE_DIRECTORY, true);
    }
}
