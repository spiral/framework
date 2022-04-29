<?php

declare(strict_types=1);

namespace Spiral\Logger;

interface ListenerRegistryInterface
{
    /**
     * Add new even listener.
     */
    public function addListener(callable $listener): self;

    /**
     * Add LogEvent listener.
     */
    public function removeListener(callable $listener): void;

    /**
     * @return callable[]
     */
    public function getListeners(): array;
}
