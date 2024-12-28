<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

/**
 * Triggered before cache item is written.
 */
final class KeyWriting extends CacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
