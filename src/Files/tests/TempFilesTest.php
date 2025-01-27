<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class TempFilesTest extends TestCase
{
    public function testTempFilename(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename();
        self::assertTrue($files->exists($tempFilename));
        self::assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        self::assertSame('sample-data', $files->read($tempFilename));
    }

    public function testTempExtension(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt');
        self::assertTrue($files->exists($tempFilename));
        self::assertSame('txt', $files->extension($tempFilename));
        self::assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        self::assertSame('sample-data', $files->read($tempFilename));
    }

    public function testTempCustomLocation(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt', self::FIXTURE_DIRECTORY);
        self::assertTrue($files->exists($tempFilename));

        self::assertSame('txt', $files->extension($tempFilename));
        self::assertSame($files->normalizePath(self::FIXTURE_DIRECTORY, true), $files->normalizePath(\dirname($tempFilename), true));

        self::assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        self::assertSame('sample-data', $files->read($tempFilename));
    }

    public function testAutoRemovalFilesWithExtensions(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt');
        self::assertTrue($files->exists($tempFilename));
        self::assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        self::assertSame('sample-data', $files->read($tempFilename));

        $files->__destruct();

        self::assertFileDoesNotExist($tempFilename);
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
