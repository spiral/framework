<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Snapshots\StorageSnapshot;

class StorageReporter implements ExceptionReporterInterface
{
    public function __construct(
        private StorageSnapshot $storageSnapshot,
    ) {
    }

    public function report(\Throwable $exception): void
    {
        $this->storageSnapshot->create($exception);
    }
}
