<?php

namespace Spiral\Cache\Event;

final class KeyWriting extends AbstractCacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
