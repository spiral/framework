<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\KernelInterface;

final class Bootstrapped
{
    public function __construct(
        public readonly KernelInterface $kernel
    ) {
    }
}
