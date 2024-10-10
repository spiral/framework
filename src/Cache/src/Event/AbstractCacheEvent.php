<?php

namespace Spiral\Cache\Event;

abstract class AbstractCacheEvent
{
    public function __construct(
        public readonly string $key,
    ) {
    }
}
