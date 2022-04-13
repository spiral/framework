<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Psr\Container\ContainerInterface;
use Spiral\Exceptions\ErrorReporterInterface;
use Spiral\Snapshots\SnapshotterInterface;

class SnapshotterReporter implements ErrorReporterInterface
{
    private ?SnapshotterInterface $snapshotter = null;
    public function __construct(
        ContainerInterface $container
    ) {
        if ($container->has(SnapshotterInterface::class)) {
            $this->snapshotter = $container->get(SnapshotterInterface::class);
        }
    }

    public function report(\Throwable $exception): void
    {
        $this->snapshotter?->register($exception);
    }
}
