<?php

declare(strict_types=1);

namespace Spiral\Tests\Snapshots;

use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Renderer\PlainRenderer;
use Spiral\Exceptions\Verbosity;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Snapshots\FileSnapshot;

final class FileSnapshotTest extends TestCase
{
    private FilesInterface $files;
    private FileSnapshot $fileSnapshot;

    protected function setUp(): void
    {
        $this->files = new Files();
        $this->fileSnapshot = new FileSnapshot(
            __DIR__ . '/snapshots',
            1,
            Verbosity::DEBUG,
            new PlainRenderer(),
            $this->files
        );
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory(__DIR__ . '/snapshots');
    }

    public function testCreate(): void
    {
        $e = new \Error('message');
        $s = $this->fileSnapshot->create($e);

        self::assertSame($e, $s->getException());

        self::assertStringContainsString('Error', $s->getMessage());
        self::assertStringContainsString('message', $s->getMessage());
        self::assertStringContainsString(__FILE__, $s->getMessage());
        self::assertStringContainsString('38', $s->getMessage());
        self::assertCount(1, $this->files->getFiles(__DIR__ . '/snapshots'));
    }

    public function testCreateBiggerThanMaxFiles(): void
    {
        $e = new \Error('message');
        $s = $this->fileSnapshot->create($e);

        $e2 = new \Error('message');
        $s2 = $this->fileSnapshot->create($e2);

        self::assertSame($e, $s->getException());
        self::assertSame($e2, $s2->getException());

        self::assertCount(1, $this->files->getFiles(__DIR__ . '/snapshots'));
    }
}
