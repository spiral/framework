<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

final class CacheHit
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value
    ) {
    }
}
