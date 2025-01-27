<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

/**
 * Triggered when cache item is successfully retrieved.
 */
final class CacheHit extends CacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
