<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Bootloaders;

use Spiral\Boot\KernelInterface;
use Spiral\Console\CommandLocator;
use Spiral\Console\ConsoleCore;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Console\LocatorInterface;
use Spiral\Core\Bootloaders\Bootloader;

class ConsoleBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        ConsoleCore::class      => ConsoleCore::class,
        LocatorInterface::class => CommandLocator::class
    ];

    /**
     * @param KernelInterface   $kernel
     * @param ConsoleDispatcher $console
     */
    public function boot(KernelInterface $kernel, ConsoleDispatcher $console)
    {
        $kernel->addDispatcher($console);
    }
}