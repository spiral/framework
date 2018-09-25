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
    const SINGLETONS = [
        FinalizerInterface::class => Finalizer::class,
    ];
}