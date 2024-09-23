<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Spiral\Logger\Event\LogEvent;

/**
 * Registry for log event listeners.
 *
 * When log event is triggered, all listeners will be executed.
 */
interface ListenerRegistryInterface
{
    /**
     * Add new even listener.
     *
     * @param callable(LogEvent): void $listener
     */
    public function addListener(callable $listener): self;

    /**
     * Add LogEvent listener.
     *
     * @param callable(LogEvent): void $listener
     */
    public function removeListener(callable $listener): void;

    /**
     * @return array<callable(LogEvent): void>
     */
    public function getListeners(): array;
}
