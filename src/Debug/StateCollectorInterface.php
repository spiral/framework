<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

/**
 * Describes application state for the external analyzers, exception and profiling handlers.
 */
interface StateCollectorInterface
{
    /**
     * @param StateInterface $state
     */
    public function populate(StateInterface $state): void;
}
