<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Debug;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\DebugBootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Debug\StateCollector\HttpCollector;

/**
 * Describes the user request and it's data.
 */
final class HttpCollectorBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        DebugBootloader::class
    ];

    protected const SINGLETONS = [
        HttpCollector::class => HttpCollector::class
    ];

    /**
     * @param HttpCollector      $httpCollector
     * @param HttpBootloader     $http
     * @param DebugBootloader    $debug
     * @param FinalizerInterface $finalizer
     */
    public function boot(
        HttpCollector $httpCollector,
        HttpBootloader $http,
        DebugBootloader $debug,
        FinalizerInterface $finalizer
    ): void {
        $http->addMiddleware(HttpCollector::class);

        $debug->addStateCollector($httpCollector);
        $finalizer->addFinalizer([$httpCollector, 'reset']);
    }
}
