<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Files;

class ConversionTest extends TestCase
{
    public function testNormalizeFilePath(): void
    {
        $files = new Files();

        $this->assertSame('/abc/file.name', $files->normalizePath('/abc\\file.name'));
        $this->assertSame('/abc/file.name', $files->normalizePath('\\abc//file.name'));
    }

    public function testNormalizeDirectoryPath(): void
    {
        $files = new Files();

        $this->assertSame('/abc/dir/', $files->normalizePath('\\abc/dir', true));
        $this->assertSame('/abc/dir/', $files->normalizePath('\\abc//dir', true));
    }

    public function testRelativePath(): void
    {
        $files = new Files();

        $this->assertSame(
            'some-filename.txt',
            $files->relativePath('/abc/some-filename.txt', '/abc')
        );

        $this->assertSame(
            '../some-filename.txt',
            $files->relativePath('/abc/../some-filename.txt', '/abc')
        );

        $this->assertSame(
            '../../some-filename.txt',
            $files->relativePath('/abc/../../some-filename.txt', '/abc')
        );

        $this->assertSame(
            './some-filename.txt',
            $files->relativePath('/abc/some-filename.txt', '/abc/..')
        );

        $this->assertSame(
            '../some-filename.txt',
            $files->relativePath('/abc/some-filename.txt', '/abc/../..')
        );
    }
}
