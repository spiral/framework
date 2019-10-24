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
use Spiral\Logger\LogFactory;
use Spiral\Logger\LogsInterface;

final class DebugBootloader extends Bootloader
{
    protected const SINGLETONS = [
        Dumper::class        => Dumper::class,
        LogsInterface::class => LogFactory::class
    ];
}
