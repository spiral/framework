<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\DispatcherInterface;

final class DispatcherFound
{
    public function __construct(
        public readonly DispatcherInterface $dispatcher
    ) {
    }
}
