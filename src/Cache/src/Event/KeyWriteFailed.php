<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

/**
 * Triggered when cache write operation failed.
 */
final class KeyWriteFailed extends CacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
