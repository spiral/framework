<?php

declare(strict_types=1);

namespace Spiral\Events;

interface ListenerRegistryInterface
{
    public function addListener(string $event, callable $listener, int $priority = 0): void;
}
