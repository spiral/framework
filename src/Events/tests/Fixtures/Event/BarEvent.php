<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Event;

final class BarEvent
{
    public function __construct(
        public readonly string $some,
    ) {}
}
