<?php

declare(strict_types=1);

namespace Spiral\Events;

interface ListenerRegistryInterface
{
    /**
     * @param class-string $event
     */
    public function addListener(string $event, callable $listener, int $priority = 0): void;
}
