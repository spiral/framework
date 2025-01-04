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
        self::assertSame('/abc/file.name', $this->files->normalizePath('/abc\\file.name'));
        self::assertSame('/abc/file.name', $this->files->normalizePath('\\abc//file.name'));
    }

    public function testNormalizeDirectoryPath(): void
    {
        self::assertSame('/abc/dir/', $this->files->normalizePath('\\abc/dir', true));
        self::assertSame('/abc/dir/', $this->files->normalizePath('\\abc//dir', true));
    }

    public function testNormalizeUniversalNamingConventionPath(): void
    {
        self::assertSame('//host/path/resource', $this->files->normalizePath('//host/path/resource'));
        self::assertSame('//host/path/resource', $this->files->normalizePath('//host/path//resource'));
        self::assertSame('\\\\host/path/resource', $this->files->normalizePath('\\\\host/path/resource'));
        self::assertSame('\\\\host/path/resource', $this->files->normalizePath('\\\\host/path//resource'));
    }

    public function testRelativePath(): void
    {
        self::assertSame('some-filename.txt', $this->files->relativePath('/abc/some-filename.txt', '/abc'));

        self::assertSame('../some-filename.txt', $this->files->relativePath('/abc/../some-filename.txt', '/abc'));

        self::assertSame('../../some-filename.txt', $this->files->relativePath('/abc/../../some-filename.txt', '/abc'));

        self::assertSame('./some-filename.txt', $this->files->relativePath('/abc/some-filename.txt', '/abc/..'));

        self::assertSame('../some-filename.txt', $this->files->relativePath('/abc/some-filename.txt', '/abc/../..'));
    }
}
