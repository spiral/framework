<?php

declare(strict_types=1);

namespace Framework\Bootloader\Exceptions;

use Spiral\Snapshots\FileSnapshooter;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Tests\Framework\BaseTest;

final class SnapshotsBootloaderTest extends BaseTest
{
    public function testSnapshotterInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            SnapshotterInterface::class,
            FileSnapshooter::class
        );
    }
}
