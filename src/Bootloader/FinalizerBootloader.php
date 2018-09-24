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
        FinalizerInterface::class => Finalizer::class
    ];

    /**
     * @param FinalizerInterface $finalizer
     */
    public function boot(FinalizerInterface $finalizer)
    {
        if (function_exists('gc_collect_cycles')) {
            $finalizer->addFinalizer('gc_collect_cycles');
        }
    }
}