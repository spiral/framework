<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Debug;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\DebugBootloader;
use Spiral\Debug\StateCollector\LogCollector;
use Spiral\Logger\ListenerRegistryInterface;

/**
 * Copies all application logs into debug state.
 */
final class LogCollectorBootloader extends Bootloader
{
    protected const SINGLETONS = [
        LogCollector::class => LogCollector::class,
    ];

    public function init(
        LogCollector $logCollector,
        DebugBootloader $debug,
        ListenerRegistryInterface $listenerRegistry,
        FinalizerInterface $finalizer
    ): void {
        $listenerRegistry->addListener($logCollector);

        /**
         * @psalm-suppress InvalidArgument
         */
        $debug->addStateCollector($logCollector);
        $finalizer->addFinalizer([$logCollector, 'reset']);
    }
}
