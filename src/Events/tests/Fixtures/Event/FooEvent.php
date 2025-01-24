<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Event;

final class FooEvent
{
    public function __construct(
        public readonly string $some,
    ) {}
}
