<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Bucket;
use Spiral\Storage\Visibility;

#[\PHPUnit\Framework\Attributes\Group('unit')]
class StorageTestCase extends TestCase
{
    public function testCreate(): void
    {
        $this->local->create('file.txt');

        $this->assertTrue($this->local->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteString(): void
    {
        $content = \random_bytes(64);
        $this->local->write('file.txt', $content);

        $this->assertTrue($this->local->exists('file.txt'));
        $this->assertSame($content, $this->local->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteStream(): void
    {
        $content = \random_bytes(64);
        $stream = \fopen('php://memory', 'ab+');
        \fwrite($stream, $content);

        $this->local->write('file.txt', $stream);

        $this->assertTrue($this->local->exists('file.txt'));
        $this->assertSame($content, $this->local->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testVisibility(): void
    {
        $this->markTestSkipped(
            'This test [' . __FUNCTION__ . '] returns incorrect visibility ' .
                'of files on Windows OS. ' .
            'It is required to understand the situation'
        );

        $this->local->create('file.txt');

        $public = Visibility::VISIBILITY_PUBLIC;
        $private = Visibility::VISIBILITY_PRIVATE;

        $this->local->setVisibility('file.txt', $public);
        $this->assertSame($public, $this->local->getVisibility('file.txt'));

        $this->local->setVisibility('file.txt', $private);
        $this->assertSame($private, $this->local->getVisibility('file.txt'));
    }

    public function testCopyToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->copy('source.txt', 'copy.txt');

        $this->assertTrue($this->local->exists('source.txt'));
        $this->assertSame($content, $this->local->getContents('source.txt'));

        $this->assertTrue($this->local->exists('copy.txt'));
        $this->assertSame($content, $this->local->getContents('copy.txt'));

        $this->cleanTempDirectory();
    }

    public function testCopyToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->copy('source.txt', 'copy.txt', $this->second);

        $this->assertTrue($this->local->exists('source.txt'));
        $this->assertSame($content, $this->local->getContents('source.txt'));
        $this->assertFalse($this->local->exists('copy.txt'));

        $this->assertTrue($this->second->exists('copy.txt'));
        $this->assertSame($content, $this->second->getContents('copy.txt'));
        $this->assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->move('source.txt', 'moved.txt');

        $this->assertFalse($this->local->exists('source.txt'));
        $this->assertTrue($this->local->exists('moved.txt'));
        $this->assertSame($content, $this->local->getContents('moved.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $this->local->write('source.txt', $content);
        $this->local->move('source.txt', 'moved.txt', $this->second);

        $this->assertFalse($this->local->exists('source.txt'));
        $this->assertFalse($this->local->exists('moved.txt'));

        $this->assertTrue($this->second->exists('moved.txt'));
        $this->assertSame($content, $this->second->getContents('moved.txt'));
        $this->assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testDelete(): void
    {
        $this->local->create('file.txt');
        $this->assertTrue($this->local->exists('file.txt'));

        $this->local->delete('file.txt');
        $this->assertFalse($this->local->exists('file.txt'));
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

        $this->assertSame($actual, $content);

        $this->cleanTempDirectory();
    }

    public function testExisting(): void
    {
        $this->assertFalse($this->local->exists('file.txt'));
        $this->local->create('file.txt');
        $this->assertTrue($this->local->exists('file.txt'));

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
        $this->assertGreaterThanOrEqual($now, $before);

        // Wait 1.1 seconds and then again modify file
        \usleep(1100000);

        $this->local->write('file.txt', 'content');
        $after = $this->local->getLastModified('file.txt');
        $this->assertGreaterThan($before, $after);
    }

    public function testSize(): void
    {
        $content = \random_bytes(\random_int(32, 256));
        $this->local->write('file.txt', $content);

        $this->assertSame(\strlen($content), $this->local->getSize('file.txt'));
    }

    /**
     * Note: Checking for all existing mime types is not the goal of this
     *       test; just need to check the readability of the mime type.
     */
    public function testMime(): void
    {
        $this->local->write('file.txt', 'content');

        $this->assertSame('text/plain', $this->local->getMimeType('file.txt'));
    }
}
