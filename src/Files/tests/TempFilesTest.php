<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

class TempFilesTest extends TestCase
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

    public function testTempFilename(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename();
        $this->assertTrue($files->exists($tempFilename));
        $this->assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        $this->assertSame('sample-data', $files->read($tempFilename));
    }

    public function testTempExtension(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt');
        $this->assertTrue($files->exists($tempFilename));
        $this->assertSame('txt', $files->extension($tempFilename));
        $this->assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        $this->assertSame('sample-data', $files->read($tempFilename));
    }

    public function testTempCustomLocation(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt', self::FIXTURE_DIRECTORY);
        $this->assertTrue($files->exists($tempFilename));

        $this->assertSame('txt', $files->extension($tempFilename));
        $this->assertSame(
            $files->normalizePath(self::FIXTURE_DIRECTORY, true),
            $files->normalizePath(dirname($tempFilename), true)
        );

        $this->assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        $this->assertSame('sample-data', $files->read($tempFilename));
    }

    public function testAutoRemovalFilesWithExtensions(): void
    {
        $files = new Files();

        $tempFilename = $files->tempFilename('txt');
        $this->assertTrue($files->exists($tempFilename));
        $this->assertSame('', $files->read($tempFilename));

        $files->write($tempFilename, 'sample-data');
        $this->assertSame('sample-data', $files->read($tempFilename));

        $files->__destruct();

        // Note: assertFileNotExists() is deprecated since phpunit 9.0, but
        // assertFileDoesNotExist() no implemented in phpunit 8.0
        // (for PHP 7.2 compatibility).
        $this->assertFalse(is_file($tempFilename));
    }
}
