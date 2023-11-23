<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;

final class BootloaderChecker implements BootloaderCheckerInterface
{
    public function __construct(
        private readonly CheckerRegistryInterface $registry = new CheckerRegistry(),
    ) {
    }

    public function canInitialize(BootloaderInterface|string $bootloader, ?BootloadConfig $config = null): bool
    {
        foreach ($this->registry->getCheckers() as $checker) {
            if (!$checker->canInitialize($bootloader, $config)) {
                return false;
            }
        }

        return true;
    }
}
