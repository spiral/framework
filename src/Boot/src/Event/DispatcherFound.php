<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\DispatcherInterface;

/**
 * The Event will be fired when a dispatcher for handling incoming
 * requests in a current environment is found.
 */
final class DispatcherFound
{
    public function __construct(
        public readonly DispatcherInterface $dispatcher
    ) {
    }
}
