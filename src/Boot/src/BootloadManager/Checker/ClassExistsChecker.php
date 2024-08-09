<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\Exception\ClassNotFoundException;

final class ClassExistsChecker implements BootloaderCheckerInterface
{
    /**
     * @throws ClassNotFoundException
     */
    public function canInitialize(BootloaderInterface|string $bootloader, ?BootloadConfig $config = null): bool
    {
        if (!\is_string($bootloader)) {
            return true;
        }

        if (!\class_exists($bootloader)) {
            throw new ClassNotFoundException(\sprintf('Bootloader class `%s` does not exist.', $bootloader));
        }

        return true;
    }
}
