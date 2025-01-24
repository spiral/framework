<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use Spiral\Storage\Visibility;

#[\PHPUnit\Framework\Attributes\Group('unit')]
class FileTestCase extends TestCase
{
    public function testPathname(): void
    {
        $file = $this->local->file('path/to/file.txt');

        self::assertSame('path/to/file.txt', $file->getPathname());
    }

    public function testStorage(): void
    {
        $file = $this->local->file('path/to/file.txt');

        self::assertSame($this->local, $file->getBucket());
    }

    public function testCreating(): void
    {
        $file = $this->local->file('path/to/file.txt');
        self::assertFalse($file->exists());

        $file->create();
        self::assertTrue($file->exists());

        $content = \random_bytes(64);
        $file->write($content);

        // execute "create" method again
        self::assertSame($content, $file->getContents());
        $file->create();
        // content must not be changed
        self::assertSame($content, $file->getContents());
    }

    public function testWriteString(): void
    {
        $content = \random_bytes(64);

        $file = $this->local->file('file.txt');
        $file->write($content);

        self::assertTrue($file->exists());
        self::assertSame($content, $file->getContents());

        $this->cleanTempDirectory();
    }

    public function testWriteStream(): void
    {
        $content = \random_bytes(64);
        $stream = \fopen('php://memory', 'ab+');
        \fwrite($stream, $content);

        $file = $this->local->file('file.txt');
        $file->write($content);

        self::assertTrue($file->exists());
        self::assertSame($content, $file->getContents());

        $this->cleanTempDirectory();
    }

    public function testVisibility(): void
    {
        $this->markTestSkipped(
            'This test [' . __FUNCTION__ . '] returns incorrect visibility ' .
                'of files on Windows OS. ' .
            'It is required to understand the situation',
        );

        $file = $this->local->file('file.txt')
            ->create()
        ;

        $public = Visibility::VISIBILITY_PUBLIC;
        $private = Visibility::VISIBILITY_PRIVATE;

        $file->setVisibility($public);
        self::assertSame($public, $file->getVisibility());

        $file->setVisibility($private);
        self::assertSame($private, $file->getVisibility());
    }

    public function testCopyToSameStorage(): void
    {
        $content = \random_bytes(64);
        $source = $this->local->file('file.txt');
        $source->write($content);

        $copy = $source->copy('copy.txt');

        self::assertTrue($source->exists());
        self::assertSame($content, $source->getContents());

        self::assertTrue($copy->exists());
        self::assertSame($content, $copy->getContents());

        $this->cleanTempDirectory();
    }

    public function testCopyToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $source = $this->local->file('source.txt');
        $source->write($content);

        $copy = $source->copy('copy.txt', $this->second);

        self::assertTrue($source->exists());
        self::assertSame($content, $source->getContents());
        self::assertFalse($this->local->exists('copy.txt'));

        self::assertTrue($copy->exists());
        self::assertSame($content, $copy->getContents());
        self::assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToSameStorage(): void
    {
        $content = \random_bytes(64);
        $source = $this->local->file('file.txt');
        $source->write($content);

        $moved = $source->move('moved.txt');

        self::assertFalse($source->exists());
        self::assertTrue($moved->exists());
        self::assertSame($content, $moved->getContents());

        $this->cleanTempDirectory();
    }

    public function testMoveToAnotherStorage(): void
    {
        $content = \random_bytes(64);
        $source = $this->local->file('source.txt');
        $source->write($content);

        $moved = $source->move('moved.txt', $this->second);

        self::assertFalse($source->exists());
        self::assertTrue($moved->exists());
        self::assertSame($content, $moved->getContents());

        self::assertFalse($this->local->exists('moved.txt'));
        self::assertFalse($this->second->exists('source.txt'));

        $this->cleanTempDirectory();
    }

    public function testDelete(): void
    {
        $source = $this->local->file('file.txt');
        self::assertFalse($source->exists());

        $source->create();
        self::assertTrue($source->exists());

        $source->delete();
        self::assertFalse($source->exists());
    }

    public function testReadingAsStream(): void
    {
        $content = \random_bytes(64);
        $source = $this->local->file('file.txt')
            ->write($content)
        ;

        $actual = '';
        $stream = $source->getStream();
        while (!\feof($stream)) {
            $actual .= \fread($stream, 256);
        }
        \fclose($stream);

        self::assertSame($actual, $content);

        $this->cleanTempDirectory();
    }

    public function testExisting(): void
    {
        $file = $this->local->file('file.txt');

        self::assertFalse($file->exists());
        $this->local->create('file.txt');
        self::assertTrue($file->exists());

        $this->cleanTempDirectory();
    }

    /**
     * Note: This test may fail since it focuses on a mutable value
     */
    public function testLastModified(): void
    {
        $now = (int) \floor(\microtime(true));

        $file = $this->local->file('file.txt')
            ->create()
        ;

        $before = $file->getLastModified();
        self::assertGreaterThanOrEqual($now, $before);

        // Wait 1.1 seconds and then again modify file
        \usleep(1100000);

        $file->write('content');
        $after = $file->getLastModified();
        self::assertGreaterThan($before, $after);
    }

    public function testSize(): void
    {
        $content = \random_bytes(\random_int(32, 256));
        $file = $this->local->file('file.txt')
            ->write($content)
        ;

        self::assertSame(\strlen($content), $file->getSize());
    }

    /**
     * Note: Checking for all existing mime types is not the goal of this
     *       test; just need to check the readability of the mime type.
     */
    public function testMime(): void
    {
        $file = $this->local->file('file.txt')
            ->write('content')
        ;

        self::assertSame('text/plain', $file->getMimeType());
    }
}
