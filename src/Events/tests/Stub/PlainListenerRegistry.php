<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Stub;

use Spiral\Events\ListenerRegistryInterface;

/**
 * @internal
 */
final class PlainListenerRegistry implements ListenerRegistryInterface
{
    /** @var class-string[] */
    public array $events = [];

    /** @var \Closure Last listener */
    public \Closure $listener;

    public int $priority;
    public int $listeners = 0;

    public function addListener(string $event, callable $listener, int $priority = 0): void
    {
        $this->events[] = $event;
        $this->listener = $listener(...);
        $this->priority = $priority;
        ++$this->listeners;
    }
}
