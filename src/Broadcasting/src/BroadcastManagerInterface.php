<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

interface BroadcastManagerInterface
{
    /**
     * Get the broadcast driver by name.
     * If name is null - return default driver.
     */
    public function connection(?string $name = null): BroadcastInterface;
}
