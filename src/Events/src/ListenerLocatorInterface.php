<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Events\Config\EventListener;

interface ListenerLocatorInterface
{
    /**
     * @psalm-return \Generator<EventListener>
     */
    public function findListeners(): \Generator;
}
