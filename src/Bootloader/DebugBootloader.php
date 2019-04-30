<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Debug\Dumper;

final class DebugBootloader extends Bootloader
{
    const SINGLETONS = [
        Dumper::class => Dumper::class,
    ];
}