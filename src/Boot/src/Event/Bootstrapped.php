<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\KernelInterface;

/**
 * The Event will be fired when all bootloaders from SYSTEM, LOAD and APP sections initialized
 */
final class Bootstrapped
{
    public function __construct(
        public readonly KernelInterface $kernel
    ) {
    }
}
