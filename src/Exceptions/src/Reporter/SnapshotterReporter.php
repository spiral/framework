<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Spiral\Exceptions\ErrorReporterInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Snapshots\SnapshotterInterface;

class SnapshotterReporter implements ErrorReporterInterface
{
    public function __construct(
        private SnapshotterInterface $snapshotter
    ) {
    }

    public function report(\Throwable $exception): void
    {
        $this->snapshotter->register($exception);
    }
}
