<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;

interface InvokerStrategyInterface
{
    /**
     * Bootload all given bootloaders.
     *
     * @param array<class-string<BootloaderInterface>>|array<class-string<BootloaderInterface>, array<string,mixed>|BootloadConfig> $classes
     */
    public function invokeBootloaders(array $classes, array $bootingCallbacks, array $bootedCallbacks): void;
}
