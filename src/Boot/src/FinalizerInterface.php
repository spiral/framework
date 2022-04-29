<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Used to close resources and connections for long running processes.
 */
interface FinalizerInterface
{
    /**
     * Finalizers are executed after every request and used for garbage collection
     * or to close open connections.
     */
    public function addFinalizer(callable $finalizer): static;

    /**
     * Finalize execution.
     *
     * @param bool $terminate Set to true if finalization is caused on application termination.
     */
    public function finalize(bool $terminate = false): void;
}
