<?php

declare(strict_types=1);

namespace Spiral\Events;

use Psr\EventDispatcher\ListenerProviderInterface;

final class ListenerRegistry implements ListenerRegistryInterface, ListenerProviderInterface
{
    /** @var array<string,PrioritizedListenersForEvent> */
    protected array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        $name = $event::class;
        foreach ($this->listeners as $eventName => $eventListeners) {
        }

        return \array_filter($this->sorted);
    }

    public function addListener(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
    }
}
