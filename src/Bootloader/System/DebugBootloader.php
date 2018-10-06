<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\System;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Debug\Benchmarker;
use Spiral\Debug\BenchmarkerInterface;
use Spiral\Debug\Dumper;

class DebugBootloader extends Bootloader
{
    const SINGLETONS = [
        BenchmarkerInterface::class => Benchmarker::class,
        Dumper::class               => Dumper::class,
    ];
}