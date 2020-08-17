<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Exception\FilesException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class DirectoriesTest extends TestCase
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

    public function testEnsureDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));
    }

    public function testEnsureExistedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        mkdir($directory);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));
    }

    public function testEnsureNestedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));
    }

    public function testEnsureNestedDirectoryNoRecursivePermissions(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory, Files::DEFAULT_FILE_MODE, false);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));
    }

    public function testEnsureExistedNestedDirectory(): void
    {
        $files = new Files();
        $directory = self::FIXTURE_DIRECTORY . 'directory/sub/other';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        mkdir(self::FIXTURE_DIRECTORY . 'directory');
        mkdir(self::FIXTURE_DIRECTORY . 'directory/sub');
        mkdir(self::FIXTURE_DIRECTORY . 'directory/sub/other');

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));
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

        $this->assertFalse($files->exists($baseDirectory));
        $this->assertFalse($files->isDirectory($baseDirectory));

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($baseDirectory));
        $this->assertTrue($files->isDirectory($baseDirectory));

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            $this->assertFalse($files->exists($filename));
            $files->write($filename, 'random-data');
            $this->assertTrue($files->exists($filename));
        }

        $files->deleteDirectory($baseDirectory, true);

        $this->assertTrue($files->exists($baseDirectory));
        $this->assertTrue($files->isDirectory($baseDirectory));

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            $this->assertFalse($files->exists($filename));
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

        $this->assertFalse($files->exists($baseDirectory));
        $this->assertFalse($files->isDirectory($baseDirectory));

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        $files->ensureDirectory($directory);

        $this->assertTrue($files->exists($baseDirectory));
        $this->assertTrue($files->isDirectory($baseDirectory));

        $this->assertTrue($files->exists($directory));
        $this->assertTrue($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            $this->assertFalse($files->exists($filename));
            $files->write($filename, 'random-data');
            $this->assertTrue($files->exists($filename));
        }

        $files->deleteDirectory($baseDirectory, false);

        $this->assertFalse($files->exists($baseDirectory));
        $this->assertFalse($files->isDirectory($baseDirectory));

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->isDirectory($directory));

        foreach ($filenames as $filename) {
            $this->assertFalse($files->exists($filename));
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
        $this->assertNotEmpty($files->getFiles(__DIR__));
    }

    public function testGetFilesRecursive(): void
    {
        $files = new Files();
        $this->assertNotEmpty($files->getFiles(dirname(__DIR__)));
    }

    public function testGetFilesPattern(): void
    {
        $files = new Files();
        $this->assertEmpty($files->getFiles(__DIR__, '*.jpg'));
    }

    public function testGetFilesRecursivePattern(): void
    {
        $files = new Files();
        $this->assertEmpty($files->getFiles(dirname(__DIR__), '*.jpg'));
        $this->assertNotEmpty($files->getFiles(dirname(__DIR__), '*.php'));
    }
}
