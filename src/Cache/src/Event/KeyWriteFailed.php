<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

final class KeyWriteFailed extends AbstractCacheEvent
{
    public function __construct(
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($key);
    }
}
