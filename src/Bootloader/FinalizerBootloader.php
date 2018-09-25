<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Finalizer\Finalizer;
use Spiral\Finalizer\FinalizerInterface;

class FinalizerBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        FinalizerInterface::class => Finalizer::class,
        Finalizer::class          => Finalizer::class
    ];

    /**
     * @param Finalizer $finalizer
     */
    public function boot(Finalizer $finalizer)
    {
        if (function_exists('gc_collect_cycles')) {
            $finalizer->addFinalizer('gc_collect_cycles');
        }
    }
}