<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\BootloadManager\ClassesRegistry;

final class CanBootedChecker implements BootloaderCheckerInterface
{
    public function __construct(
        private readonly ClassesRegistry $bootloaders,
    ) {
    }

    public function canInitialize(BootloaderInterface|string $bootloader, ?BootloadConfig $config = null): bool
    {
        $ref = new \ReflectionClass($bootloader);

        return !$this->bootloaders->isBooted($ref->getName())
            && !$ref->isAbstract()
            && !$ref->isInterface()
            && $ref->implementsInterface(BootloaderInterface::class);
    }
}
