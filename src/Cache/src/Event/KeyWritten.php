<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

/**
 * Triggered after cache item is written.
 */
final class KeyWritten extends CacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
