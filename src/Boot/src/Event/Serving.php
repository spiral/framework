<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

final class Serving
{
    public function __construct(
        public readonly array $dispatchers
    ) {
    }
}
