<?php

declare(strict_types=1);

namespace Spiral\Queue\Failed;

use Spiral\Snapshots\SnapshotterInterface;

final class LogFailedJobHandler implements FailedJobHandlerInterface
{
    public function __construct(
        private readonly SnapshotterInterface $snapshotter
    ) {
    }

    public function handle(string $driver, string $queue, string $job, array $payload, \Throwable $e): void
    {
        $this->snapshotter->register($e);
    }
}
