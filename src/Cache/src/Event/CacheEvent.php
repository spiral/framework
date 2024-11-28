<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

/**
 * Base class for all cache events.
 */
abstract class CacheEvent
{
    public function __construct(
        public readonly string $key,
    ) {
    }
}
