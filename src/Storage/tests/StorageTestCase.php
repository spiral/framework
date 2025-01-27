<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use Spiral\Storage\Visibility;

#[\PHPUnit\Framework\Attributes\Group('unit')]
class StorageTestCase extends TestCase
{
    public function testCreate(): void
    {
        $this->local->create('file.txt');

        self::assertTrue($this->local->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteString(): void
    {
        $content = \random_bytes(64);
        $this->local->write('file.txt', $content);

        self::assertTrue($this->local->exists('file.txt'));
        self::assertSame($content, $this->local->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteStream(): void
    {
        $content = \random_bytes(64);
        $stream = \fopen('php://memory', 'ab+');
        \fwrite($stream, $content);

        $this->local->write('file.txt', $stream);

        self::assertTrue($this->local->exists('file.txt'));
        self::assertSame($content, $this->local->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testVisibility(): void
    {
        $this->markTestSkipped(
            'This test [' . __FUNCTION__ . '] returns incorrect visibility ' .
                'of files on Windows OS. ' .
            'It is required to understand the situation',
        );

        $this->local->create('file.txt');

        $public = Visibility::VISIBILITY_PUBLIC;
        $private = Visibility::VISIBILITY_PRIVATE;

        $this->local->setVisibility('file.txt', $public);
        self::assertSame($public, $this->local->getVisibility('file.txt'));

        $this->local->setVisibility('file.txt', $private);
        self::assertSame($private, $this->local->getVisibility('file.txt'));
    }

    public function testCopyToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->copy('source.txt', 'copy.txt');

        self::assertTrue($this->local->exists('source.txt'));
        self::assertSame($content, $this->local->getContents('source.txt'));

        self::assertTrue($this->local->exists('copy.txt'));
        self::assertSame($content, $this->local->getContents('copy.txt'));

        $this->cleanTempDirectory();
    }

    public function testCopyToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->copy('source.txt', 'copy.txt', $this->second);

        self::assertTrue($this->local->exists('source.txt'));
        self::assertSame($content, $this->local->getContents('source.txt'));
        self::assertFalse($this->local->exists('copy.txt'));

        self::assertTrue($this->second->exists('copy.txt'));
        self::assertSame($content, $this->second->getContents('copy.txt'));
        self::assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->move('source.txt', 'moved.txt');

        self::assertFalse($this->local->exists('source.txt'));
        self::assertTrue($this->local->exists('moved.txt'));
        self::assertSame($content, $this->local->getContents('moved.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->move('source.txt', 'moved.txt', $this->second);

        self::assertFalse($this->local->exists('source.txt'));
        self::assertFalse($this->local->exists('moved.txt'));

        self::assertTrue($this->second->exists('moved.txt'));
        self::assertSame($content, $this->second->getContents('moved.txt'));
        self::assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testDelete(): void
    {
        $this->local->create('file.txt');
        self::assertTrue($this->local->exists('file.txt'));

        $this->local->delete('file.txt');
        self::assertFalse($this->local->exists('file.txt'));
    }

    public function testReadingAsStream(): void
    {
        $content = \random_bytes(64);
        $this->local->write('file.txt', $content);

        $actual = '';
        $stream = $this->local->getStream('file.txt');
        while (!\feof($stream)) {
            $actual .= \fread($stream, 256);
        }
        \fclose($stream);

        self::assertSame($actual, $content);

        $this->cleanTempDirectory();
    }

    public function testExisting(): void
    {
        self::assertFalse($this->local->exists('file.txt'));
        $this->local->create('file.txt');
        self::assertTrue($this->local->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    /**
     * Note: This test may fail since it focuses on a mutable value
     */
    public function testLastModified(): void
    {
        $now = (int) \floor(\microtime(true));

        $this->local->create('file.txt');
        $before = $this->local->getLastModified('file.txt');
        self::assertGreaterThanOrEqual($now, $before);

        // Wait 1.1 seconds and then again modify file
        \usleep(1100000);

        $this->local->write('file.txt', 'content');
        $after = $this->local->getLastModified('file.txt');
        self::assertGreaterThan($before, $after);
    }

    public function testSize(): void
    {
        $content = \random_bytes(\random_int(32, 256));
        $this->local->write('file.txt', $content);

        self::assertSame(\strlen($content), $this->local->getSize('file.txt'));
    }

    /**
     * Note: Checking for all existing mime types is not the goal of this
     *       test; just need to check the readability of the mime type.
     */
    public function testMime(): void
    {
        $this->local->write('file.txt', 'content');

        self::assertSame('text/plain', $this->local->getMimeType('file.txt'));
    }
}
