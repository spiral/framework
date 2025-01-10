<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class InformationTest extends TestCase
{
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

    public function testTime(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $files->write($filename, 'data', FilesInterface::READONLY);
        self::assertEquals(filemtime($filename), $files->time($filename));
    }

    public function testTimeMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $files->time($filename);
    }

    public function testMD5(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $files->write($filename, 'data');
        self::assertEquals(md5_file($filename), $files->md5($filename));
        self::assertSame(md5('data'), $files->md5($filename));
    }

    public function testMD5MissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $files->md5($filename);
    }

    public function testExtension(): void
    {
        $files = new Files();

        self::assertSame('txt', $files->extension('test.txt'));
        self::assertSame('txt', $files->extension('test.TXT'));
        self::assertSame('txt', $files->extension('test.data.TXT'));
    }

    public function testExists(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        self::assertSame(file_exists($filename), $files->exists($filename));

        $files->write($filename, 'data');
        self::assertTrue($files->exists($filename));
        self::assertSame(file_exists($filename), $files->exists($filename));
    }

    public function testSize(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->write($filename, 'some-data-string');
        self::assertTrue($files->exists($filename));

        self::assertSame(strlen('some-data-string'), $files->size($filename));
    }

    public function testSizeMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->size($filename);
    }

    public function testLocalUri(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->write($filename, 'data');
        self::assertSame($filename, $filename);
    }

    public function testIsFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->write($filename, 'data');
        self::assertTrue($files->exists($filename));

        self::assertTrue($files->isFile($filename));
        self::assertSame(is_file($filename), $files->isFile($filename));

        self::assertFalse($files->isDirectory($filename));
        self::assertSame(is_dir($filename), $files->isDirectory($filename));
    }

    public function testIsMissingFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));

        self::assertFalse($files->isFile($filename));
        self::assertSame(is_file($filename), $files->isFile($filename));

        self::assertFalse($files->isDirectory($filename));
        self::assertSame(is_dir($filename), $files->isDirectory($filename));
    }

    public function testIsDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory/';

        self::assertFalse($files->exists($directory));
        $files->ensureDirectory($directory);
        self::assertTrue($files->exists($directory));

        self::assertFalse($files->isFile($directory));
        self::assertSame(is_file($directory), $files->isFile($directory));

        self::assertTrue($files->isDirectory($directory));
        self::assertSame(is_dir($directory), $files->isDirectory($directory));
    }

    public function testIsMissingDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory/';

        self::assertFalse($files->exists($directory));

        self::assertFalse($files->isFile($directory));
        self::assertSame(is_file($directory), $files->isFile($directory));

        self::assertFalse($files->isDirectory($directory));
        self::assertSame(is_dir($directory), $files->isDirectory($directory));
    }

    public function testIsDirectoryNoSlash(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory';

        self::assertFalse($files->exists($directory));
        $files->ensureDirectory($directory);
        self::assertTrue($files->exists($directory));

        self::assertFalse($files->isFile($directory));
        self::assertSame(is_file($directory), $files->isFile($directory));

        self::assertTrue($files->isDirectory($directory));
        self::assertSame(is_dir($directory), $files->isDirectory($directory));
    }
}
