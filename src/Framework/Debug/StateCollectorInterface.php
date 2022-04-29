<?php

declare(strict_types=1);

namespace Spiral\Debug;

/**
 * Describes application state for the external analyzers, exception and profiling handlers.
 */
interface StateCollectorInterface
{
    public function populate(StateInterface $state): void;
}
