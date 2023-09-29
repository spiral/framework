<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

final class StorageSnapshooter implements SnapshotterInterface
{
    public function __construct(
        private readonly StorageSnapshot $storageSnapshot
    ) {
    }

    public function register(\Throwable $e): SnapshotInterface
    {
        return $this->storageSnapshot->create($e);
    }
}
