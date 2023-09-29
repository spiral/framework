<?php

declare(strict_types=1);

namespace Spiral\Tests\Snapshots;

use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Snapshots\StorageSnapshot;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\FileInterface;
use Spiral\Storage\StorageInterface;

final class StorageSnapshotTest extends TestCase
{
    private ExceptionRendererInterface $renderer;
    private FileInterface $file;
    private BucketInterface $bucket;
    private StorageInterface $storage;

    protected function setUp(): void
    {
        $this->renderer = $this->createMock(ExceptionRendererInterface::class);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->willReturn('foo');

        $this->file = $this->createMock(FileInterface::class);
        $this->file
            ->expects($this->once())
            ->method('write')
            ->with('foo');

        $this->bucket = $this->createMock(BucketInterface::class);

        $this->storage = $this->createMock(StorageInterface::class);
        $this->storage
            ->expects($this->once())
            ->method('bucket')
            ->willReturn($this->bucket);
    }

    public function testCreate(): void
    {
        $this->bucket
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static fn (string $filename) => \str_contains($filename, 'Error.txt')))
            ->willReturn($this->file);

        $e = new \Error('message');
        $s = (new StorageSnapshot('foo', $this->storage, Verbosity::VERBOSE, $this->renderer))->create($e);

        $this->assertSame($e, $s->getException());

        $this->assertStringContainsString('Error', $s->getMessage());
        $this->assertStringContainsString('message', $s->getMessage());
        $this->assertStringContainsString(__FILE__, $s->getMessage());
        $this->assertStringContainsString('53', $s->getMessage());
    }

    public function testCreateWithDirectory(): void
    {
        $this->bucket
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static fn (string $filename) => \str_starts_with($filename, 'foo/bar')))
            ->willReturn($this->file);

        $e = new \Error('message');
        $s = (new StorageSnapshot('foo', $this->storage, Verbosity::VERBOSE, $this->renderer, 'foo/bar'))
            ->create($e);

        $this->assertSame($e, $s->getException());

        $this->assertStringContainsString('Error', $s->getMessage());
        $this->assertStringContainsString('message', $s->getMessage());
        $this->assertStringContainsString(__FILE__, $s->getMessage());
        $this->assertStringContainsString('72', $s->getMessage());
    }
}
