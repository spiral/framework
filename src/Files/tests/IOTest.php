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
use Spiral\Files\Exception\FilesException;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class IOTest extends TestCase
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

    public function testWrite(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $this->assertFalse($files->exists($filename));

        $files->write($filename, 'some-data');
        $this->assertTrue($files->exists($filename));

        $this->assertSame('some-data', file_get_contents($filename));
    }

    public function testWriteAndEnsureDirectory(): void
    {
        $files = new Files();

        $directory = self::FIXTURE_DIRECTORY . '/directory/abc/';
        $filename = $directory . 'test.txt';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->exists($filename));

        $this->assertFalse($files->isDirectory($directory));

        $files->write($filename, 'some-data', FilesInterface::READONLY, true);

        $this->assertTrue($files->isDirectory($directory));
        $this->assertTrue($files->exists($filename));
        $this->assertSame('some-data', file_get_contents($filename));
    }

    public function testRead(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $this->assertFalse($files->exists($filename));

        $files->write($filename, 'some-data');
        $this->assertTrue($files->exists($filename));

        $this->assertSame('some-data', $files->read($filename));
    }

    public function testReadMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $this->assertFalse($files->exists($filename));

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
        $this->assertFalse($files->exists($filename));

        $files->append($filename, 'some-data');
        $this->assertTrue($files->exists($filename));

        $this->assertSame('some-data', file_get_contents($filename));

        $files->append($filename, ';other-data');
        $this->assertSame('some-data;other-data', file_get_contents($filename));
    }

    public function testAppendEnsureDirectory(): void
    {
        $files = new Files();

        $directory = self::FIXTURE_DIRECTORY . '/directory/abc/';
        $filename = $directory . 'test.txt';

        $this->assertFalse($files->exists($directory));
        $this->assertFalse($files->exists($filename));

        $this->assertFalse($files->isDirectory($directory));

        $files->append($filename, 'some-data', null, true);

        $this->assertTrue($files->isDirectory($directory));
        $this->assertTrue($files->exists($filename));
        $this->assertSame('some-data', file_get_contents($filename));

        $files->append($filename, ';other-data', null, true);
        $this->assertSame('some-data;other-data', file_get_contents($filename));
    }

    public function testTouch(): void
    {
        $files = new Files();

        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->touch($filename);
        $this->assertTrue($files->exists($filename));
    }

    public function testDelete(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));

        $files->touch($filename);
        $this->assertTrue($files->exists($filename));

        $files->delete($filename);
        $this->assertFalse($files->exists($filename));
    }

    public function testDeleteMissingFile(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';

        $this->assertFalse($files->exists($filename));
        $files->delete($filename);
    }

    public function testCopy(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        $this->assertFalse($files->exists($filename));
        $files->write($filename, 'some-data');

        $this->assertTrue($files->exists($filename));
        $this->assertSame('some-data', file_get_contents($filename));

        $this->assertFalse($files->exists($destination));

        $this->assertTrue($files->copy($filename, $destination));
        $this->assertTrue($files->exists($destination));
        $this->assertTrue($files->exists($filename));

        $this->assertSame(file_get_contents($filename), file_get_contents($destination));
    }

    public function testCopyMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        $this->assertFalse($files->exists($filename));
        $files->copy($filename, $destination);
    }

    public function testMove(): void
    {
        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        $this->assertFalse($files->exists($filename));
        $files->write($filename, 'some-data');

        $this->assertTrue($files->exists($filename));
        $this->assertSame('some-data', file_get_contents($filename));

        $this->assertFalse($files->exists($destination));

        $this->assertTrue($files->move($filename, $destination));
        $this->assertTrue($files->exists($destination));
        $this->assertFalse($files->exists($filename));

        $this->assertSame('some-data', file_get_contents($destination));
    }

    public function testMoveMissingFile(): void
    {
        $this->expectExceptionMessageMatches("/File '.*test.txt' not found/");
        $this->expectException(FileNotFoundException::class);

        $files = new Files();
        $filename = self::FIXTURE_DIRECTORY . '/test.txt';
        $destination = self::FIXTURE_DIRECTORY . '/new.txt';

        $this->assertFalse($files->exists($filename));
        $files->move($filename, $destination);
    }
}
