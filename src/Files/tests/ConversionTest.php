<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use Spiral\Files\Files;

final class ConversionTest extends TestCase
{
    private Files $files;

    protected function setUp(): void
    {
        $this->files = new Files();
    }

    public function testNormalizeFilePath(): void
    {
        $this->assertSame('/abc/file.name', $this->files->normalizePath('/abc\\file.name'));
        $this->assertSame('/abc/file.name', $this->files->normalizePath('\\abc//file.name'));
    }

    public function testNormalizeDirectoryPath(): void
    {
        $this->assertSame('/abc/dir/', $this->files->normalizePath('\\abc/dir', true));
        $this->assertSame('/abc/dir/', $this->files->normalizePath('\\abc//dir', true));
    }

    public function testNormalizeUniversalNamingConventionPath(): void
    {
        $this->assertSame('//host/path/resource', $this->files->normalizePath('//host/path/resource'));
        $this->assertSame('//host/path/resource', $this->files->normalizePath('//host/path//resource'));
        $this->assertSame('\\\\host/path/resource', $this->files->normalizePath('\\\\host/path/resource'));
        $this->assertSame('\\\\host/path/resource', $this->files->normalizePath('\\\\host/path//resource'));
    }

    public function testRelativePath(): void
    {
        $this->assertSame(
            'some-filename.txt',
            $this->files->relativePath('/abc/some-filename.txt', '/abc')
        );

        $this->assertSame(
            '../some-filename.txt',
            $this->files->relativePath('/abc/../some-filename.txt', '/abc')
        );

        $this->assertSame(
            '../../some-filename.txt',
            $this->files->relativePath('/abc/../../some-filename.txt', '/abc')
        );

        $this->assertSame(
            './some-filename.txt',
            $this->files->relativePath('/abc/some-filename.txt', '/abc/..')
        );

        $this->assertSame(
            '../some-filename.txt',
            $this->files->relativePath('/abc/some-filename.txt', '/abc/../..')
        );
    }
}
