<?php

declare(strict_types=1);

namespace Framework\Bootloader\Exceptions;

use Spiral\Snapshots\FileSnapshooter;
use Spiral\Snapshots\FileSnapshot;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class SnapshotsBootloaderTest extends BaseTestCase
{
    public function testSnapshotterInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            SnapshotterInterface::class,
            FileSnapshooter::class,
        );
    }

    public function testFileSnapshotBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            FileSnapshot::class,
            FileSnapshot::class,
        );
    }
}
