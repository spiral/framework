<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Psr\Container\ContainerInterface;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Snapshots\SnapshotterInterface;

class SnapshotterReporter implements ExceptionReporterInterface
{
    // private ?SnapshotterInterface $snapshotter = null;
    public function __construct(
        private ContainerInterface $container,
    ) {
        // if ($container->has(SnapshotterInterface::class)) {
        //     $this->snapshotter = $container->get(SnapshotterInterface::class);
        // }
    }

    public function report(\Throwable $exception): void
    {
        $this->container->get(SnapshotterInterface::class)?->register($exception);
    }
}
