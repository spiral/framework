<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

interface CheckerRegistryInterface
{
    public function register(BootloaderCheckerInterface $checker): void;

    /**
     * @return array<BootloaderCheckerInterface>
     */
    public function getCheckers(): array;
}
