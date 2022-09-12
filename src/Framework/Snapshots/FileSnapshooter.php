<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

final class FileSnapshooter implements SnapshotterInterface
{
    public function __construct(
        private readonly FileSnapshot $fileSnapshot
    ) {
    }

    public function register(\Throwable $e): SnapshotInterface
    {
        return $this->fileSnapshot->create($e);
    }
}
