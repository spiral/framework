<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class InformationTest extends TestCase
{
    public function setUp(): void
    {
        $files = new Files();
        $files->ensureDirectory(self::FIXTURE_DIRECTORY, FilesInterface::RUNTIME);
    }

    public function tearDown(): void
    {
        $files = new Files();
        $files->deleteDirectory(self::FIXTURE_DIRECTORY, true);
    }

    public function testTime(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $files->write($filename, 'data', FilesInterface::READONLY);
        $this->assertEquals(filemtime($filename), $files->time($filename));
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
        $this->assertEquals(md5_file($filename), $files->md5($filename));
        $this->assertEquals(md5('data'), $files->md5($filename));
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

        $this->assertSame('txt', $files->extension('test.txt'));
        $this->assertSame('txt', $files->extension('test.TXT'));
        $this->assertSame('txt', $files->extension('test.data.TXT'));
    }

    public function testExists(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $this->assertSame(file_exists($filename), $files->exists($filename));

        $files->write($filename, 'data');
        $this->assertTrue($files->exists($filename));
        $this->assertSame(file_exists($filename), $files->exists($filename));
    }

    public function testSize(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->write($filename, 'some-data-string');
        $this->assertTrue($files->exists($filename));

        $this->assertSame(strlen('some-data-string'), $files->size($filename));
    }

    public function testSizeMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->size($filename);
    }

    public function testLocalUri(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->write($filename, 'data');
        $this->assertSame($filename, $filename);
    }

    public function testIsFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->write($filename, 'data');
        $this->assertTrue($files->exists($filename));

        $this->assertTrue($files->isFile($filename));
        $this->assertSame(is_file($filename), $files->isFile($filename));

        $this->assertFalse($files->isDirectory($filename));
        $this->assertSame(is_dir($filename), $files->isDirectory($filename));
    }

    public function testIsMissingFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));

        $this->assertFalse($files->isFile($filename));
        $this->assertSame(is_file($filename), $files->isFile($filename));

        $this->assertFalse($files->isDirectory($filename));
        $this->assertSame(is_dir($filename), $files->isDirectory($filename));
    }

    public function testIsDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory/';

        $this->assertFalse($files->exists($directory));
        $files->ensureDirectory($directory);
        $this->assertTrue($files->exists($directory));

        $this->assertFalse($files->isFile($directory));
        $this->assertSame(is_file($directory), $files->isFile($directory));

        $this->assertTrue($files->isDirectory($directory));
        $this->assertSame(is_dir($directory), $files->isDirectory($directory));
    }

    public function testIsMissingDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory/';

        $this->assertFalse($files->exists($directory));

        $this->assertFalse($files->isFile($directory));
        $this->assertSame(is_file($directory), $files->isFile($directory));

        $this->assertFalse($files->isDirectory($directory));
        $this->assertSame(is_dir($directory), $files->isDirectory($directory));
    }

    public function testIsDirectoryNoSlash(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . '/directory';

        $this->assertFalse($files->exists($directory));
        $files->ensureDirectory($directory);
        $this->assertTrue($files->exists($directory));

        $this->assertFalse($files->isFile($directory));
        $this->assertSame(is_file($directory), $files->isFile($directory));

        $this->assertTrue($files->isDirectory($directory));
        $this->assertSame(is_dir($directory), $files->isDirectory($directory));
    }
}
