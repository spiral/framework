<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * Declares that bootloader has other bootloaders as dependencies.
 */
interface DependedInterface
{
    /**
     * Return class names of bootloders current bootloader depends on.
     * Related bootloaders will be initiated automatically with nested
     * dependencies.
     *
     * @return array<int, class-string<BootloaderInterface>|class-string<DependedInterface>>
     */
    public function defineDependencies(): array;
}
