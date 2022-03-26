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
    protected const DEPENDENCIES = [
        DebugBootloader::class,
    ];

    protected const SINGLETONS = [
        LogCollector::class => LogCollector::class,
    ];

    /**
     * @param LogCollector              $logCollector
     * @param DebugBootloader           $debug
     * @param ListenerRegistryInterface $listenerRegistry
     * @param FinalizerInterface        $finalizer
     */
    public function boot(
        LogCollector $logCollector,
        DebugBootloader $debug,
        ListenerRegistryInterface $listenerRegistry,
        FinalizerInterface $finalizer
    ): void {
        $listenerRegistry->addListener($logCollector);

        $debug->addStateCollector($logCollector);
        $finalizer->addFinalizer([$logCollector, 'reset']);
    }
}
