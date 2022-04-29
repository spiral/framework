<?php

declare(strict_types=1);

namespace Spiral\Logger;

/**
 * Contains all log listeners.
 */
final class ListenerRegistry implements ListenerRegistryInterface
{
    /** @var callable[] */
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
        if ($key !== null) {
            unset($this->listeners[$key]);
        }
    }

    /**
     * @return callable[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }
}
