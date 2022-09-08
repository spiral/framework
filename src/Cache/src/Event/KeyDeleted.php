<?php

declare(strict_types=1);

namespace Spiral\Cache\Event;

final class KeyDeleted
{
    public function __construct(
        public readonly string $key,
    ) {
    }
}
