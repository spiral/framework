<?php

declare(strict_types=1);

namespace Framework\Bootloader\Exceptions;

use Spiral\Bootloader\StorageSnapshotsBootloader;
use Spiral\Core\Container;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Snapshots\StorageSnapshooter;
use Spiral\Snapshots\StorageSnapshot;
use Spiral\Testing\TestApp;
use Spiral\Testing\TestCase;

final class StorageSnapshotsBootloaderTest extends TestCase
{
    public const ENV = [
        'SNAPSHOTS_BUCKET' => 'foo',
    ];

    public function createAppInstance(Container $container = new Container()): TestApp
    {
        return TestApp::create(
            directories: $this->defineDirectories(
                $this->rootDirectory(),
            ),
            handleErrors: false,
            container: $container,
        )->withBootloaders([StorageSnapshotsBootloader::class]);
    }

    public function testSnapshotterInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SnapshotterInterface::class, StorageSnapshooter::class);
    }

    public function testStorageSnapshotBinding(): void
    {
        $this->assertContainerBoundAsSingleton(StorageSnapshot::class, StorageSnapshot::class);
    }
}
