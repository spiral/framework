<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

interface SnapshotInterface
{
    /**
     * Get unique exception id.
     */
    public function getID(): string;

    /**
     * Associated exception.
     */
    public function getException(): \Throwable;

    /**
     * Formatted exception message.
     */
    public function getMessage(): string;

    /**
     * Get exception description in simple array form (must be
     * json friendly).
     */
    public function describe(): array;
}
