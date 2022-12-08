<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

interface InvokerStrategyInterface
{
    /**
     * Bootload all given bootloaders.
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     */
    public function invokeBootloaders(array $classes, array $bootingCallbacks, array $bootedCallbacks): void;
}
