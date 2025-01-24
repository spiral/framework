<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class IOTest extends TestCase
{
    public function testWrite(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        self::assertFalse($files->exists($filename));

        $files->write($filename, 'some-data');
        self::assertTrue($files->exists($filename));

        self::assertSame('some-data', \file_get_contents($filename));
    }

    public function testWriteAndEnsureDirectory(): void
    {
        $files = new Files();

        $directory = self::FIXTURE_DIRECTORY . '/directory/abc/';
        $filename = $directory . 'test.txt';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->exists($filename));

        self::assertFalse($files->isDirectory($directory));

        $files->write($filename, 'some-data', FilesInterface::READONLY, true);

        self::assertTrue($files->isDirectory($directory));
        self::assertTrue($files->exists($filename));
        self::assertSame('some-data', \file_get_contents($filename));
    }

    public function testRead(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        self::assertFalse($files->exists($filename));

        $files->write($filename, 'some-data');
        self::assertTrue($files->exists($filename));

        self::assertSame('some-data', $files->read($filename));
    }

    public function testReadMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        self::assertFalse($files->exists($filename));

        $files->read($filename);
    }

    public function testWriteForbidden(): void
    {
        $this->expectException(FilesException::class);

        $files = new Files();
        $files->write(self::FIXTURE_DIRECTORY, 'data');
    }

    public function testGetPermissionsException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $files->getPermissions(self::FIXTURE_DIRECTORY . '/missing');
    }

    public function testAppend(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        self::assertFalse($files->exists($filename));

        $files->append($filename, 'some-data');
        self::assertTrue($files->exists($filename));

        self::assertSame('some-data', \file_get_contents($filename));

        $files->append($filename, ';other-data');
        self::assertSame('some-data;other-data', \file_get_contents($filename));
    }

    public function testAppendEnsureDirectory(): void
    {
        $files = new Files();

        $directory = self::FIXTURE_DIRECTORY . '/directory/abc/';
        $filename = $directory . 'test.txt';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->exists($filename));

        self::assertFalse($files->isDirectory($directory));

        $files->append($filename, 'some-data', null, true);

        self::assertTrue($files->isDirectory($directory));
        self::assertTrue($files->exists($filename));
        self::assertSame('some-data', \file_get_contents($filename));

        $files->append($filename, ';other-data', null, true);
        self::assertSame('some-data;other-data', \file_get_contents($filename));
    }

    public function testTouch(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->touch($filename);
        self::assertTrue($files->exists($filename));
    }

    public function testDelete(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));

        $files->touch($filename);
        self::assertTrue($files->exists($filename));

        $files->delete($filename);
        self::assertFalse($files->exists($filename));
    }

    public function testDeleteMissingFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        self::assertFalse($files->exists($filename));
        $files->delete($filename);
    }

    public function testCopy(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        self::assertFalse($files->exists($filename));
        $files->write($filename, 'some-data');

        self::assertTrue($files->exists($filename));
        self::assertSame('some-data', \file_get_contents($filename));

        self::assertFalse($files->exists($destination));

        self::assertTrue($files->copy($filename, $destination));
        self::assertTrue($files->exists($destination));
        self::assertTrue($files->exists($filename));

        self::assertSame(\file_get_contents($filename), \file_get_contents($destination));
    }

    public function testCopyMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        self::assertFalse($files->exists($filename));
        $files->copy($filename, $destination);
    }

    public function testMove(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        self::assertFalse($files->exists($filename));
        $files->write($filename, 'some-data');

        self::assertTrue($files->exists($filename));
        self::assertSame('some-data', \file_get_contents($filename));

        self::assertFalse($files->exists($destination));

        self::assertTrue($files->move($filename, $destination));
        self::assertTrue($files->exists($destination));
        self::assertFalse($files->exists($filename));

        self::assertSame('some-data', \file_get_contents($destination));
    }

    public function testMoveMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        self::assertFalse($files->exists($filename));
        $files->move($filename, $destination);
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
