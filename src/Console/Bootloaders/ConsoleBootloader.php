<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Bootloaders;

use Spiral\Boot\KernelInterface;
use Spiral\Config\ModifierInterface;
use Spiral\Config\Patches\AppendPatch;
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
     * @param ModifierInterface $modifier
     */
    public function boot(
        KernelInterface $kernel,
        ConsoleDispatcher $console,
        ModifierInterface $modifier
    ) {
        $kernel->addDispatcher($console);

        // register default console commands
        $modifier->modify('tokenizer', new AppendPatch(
            'directories',
            null,
            directory('spiral') . '/console/src/'
        ));

        // register default framework commands
        $modifier->modify('tokenizer', new AppendPatch(
            'directories',
            null,
            directory('spiral') . '/framework/src/'
        ));
    }
}