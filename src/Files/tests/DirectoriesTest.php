<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Exception\FilesException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class DirectoriesTest extends TestCase
{
    public function testEnsureDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));
    }

    public function testEnsureExistedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        \mkdir($directory);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));
    }

    public function testEnsureNestedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));
    }

    public function testEnsureNestedDirectoryNoRecursivePermissions(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory, Files::DEFAULT_FILE_MODE, false);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));
    }

    public function testEnsureExistedNestedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        \mkdir(self::FIXTURE_DIRECTORY . 'directory');
        \mkdir(self::FIXTURE_DIRECTORY . 'directory/sub');
        \mkdir(self::FIXTURE_DIRECTORY . 'directory/sub/other');

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));
    }

    public function testDeleteDirectoryContent(): void
    {
        $files = new Files();
        $baseDirectory = self::FIXTURE_DIRECTORY . 'directory/';
        $directory = $baseDirectory . 'sub/other';

        $filenames = [
            $baseDirectory . 'test.file',
            $directory . 'other.file',
            $directory . '.sample',
        ];

        self::assertFalse($files->exists($baseDirectory));
        self::assertFalse($files->isDirectory($baseDirectory));

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($baseDirectory));
        self::assertTrue($files->isDirectory($baseDirectory));

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            self::assertFalse($files->exists($filename));
            $files->write($filename, 'random-data');
            self::assertTrue($files->exists($filename));
        }

        $files->deleteDirectory($baseDirectory, true);

        self::assertTrue($files->exists($baseDirectory));
        self::assertTrue($files->isDirectory($baseDirectory));

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            self::assertFalse($files->exists($filename));
        }
    }

    public function testDeleteDirectory(): void
    {
        $files = new Files();
        $baseDirectory = self::FIXTURE_DIRECTORY . 'directory/';
        $directory = $baseDirectory . 'sub/other';

        $filenames = [
            $baseDirectory . 'test.file',
            $directory . 'other.file',
            $directory . '.sample',
        ];

        self::assertFalse($files->exists($baseDirectory));
        self::assertFalse($files->isDirectory($baseDirectory));

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        self::assertTrue($files->exists($baseDirectory));
        self::assertTrue($files->isDirectory($baseDirectory));

        self::assertTrue($files->exists($directory));
        self::assertTrue($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            self::assertFalse($files->exists($filename));
            $files->write($filename, 'random-data');
            self::assertTrue($files->exists($filename));
        }

        $files->deleteDirectory($baseDirectory, false);

        self::assertFalse($files->exists($baseDirectory));
        self::assertFalse($files->isDirectory($baseDirectory));

        self::assertFalse($files->exists($directory));
        self::assertFalse($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            self::assertFalse($files->exists($filename));
        }
    }

    public function testDeleteDirectoryButFile(): void
    {
        $this->expectException(FilesException::class);

        $files = new Files();

        $files->write(self::FIXTURE_DIRECTORY . 'test', 'data');
        $files->deleteDirectory(self::FIXTURE_DIRECTORY . 'test');
    }

    public function testGetFiles(): void
    {
        $files = new Files();
        self::assertNotEmpty($files->getFiles(__DIR__));
    }

    public function testGetFilesRecursive(): void
    {
        $files = new Files();
        self::assertNotEmpty($files->getFiles(\dirname(__DIR__)));
    }

    public function testGetFilesPattern(): void
    {
        $files = new Files();
        self::assertEmpty($files->getFiles(__DIR__, '*.jpg'));
    }

    public function testGetFilesRecursivePattern(): void
    {
        $files = new Files();

        self::assertEmpty($files->getFiles(__DIR__, '*.jpg'));
        self::assertNotEmpty($files->getFiles(__DIR__, '*.php'));
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
