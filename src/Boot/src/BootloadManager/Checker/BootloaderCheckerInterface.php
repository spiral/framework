<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;

interface BootloaderCheckerInterface
{
    /**
     * @param class-string<BootloaderInterface>|BootloaderInterface $bootloader
     */
    public function canInitialize(string|BootloaderInterface $bootloader, ?BootloadConfig $config = null): bool;
}
