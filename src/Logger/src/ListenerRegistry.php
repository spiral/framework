<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Spiral\Logger\Event\LogEvent;

/**
 * Contains all log listeners.
 */
final class ListenerRegistry implements ListenerRegistryInterface
{
    /** @var array<int, callable(LogEvent): void> */
    private array $listeners = [];

    public function addListener(callable $listener): self
    {
        if (!\in_array($listener, $this->listeners, true)) {
            $this->listeners[] = $listener;
        }

        return $this;
    }

    public function removeListener(callable $listener): void
    {
        $key = \array_search($listener, $this->listeners, true);
        if ($key !== false) {
            unset($this->listeners[$key]);
        }
    }

    public function getListeners(): array
    {
        return $this->listeners;
    }
}
