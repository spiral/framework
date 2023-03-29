<?php

declare(strict_types=1);

namespace Spiral\Queue\Event;

final class JobProcessed
{
    public function __construct(
        public readonly string $name,
        public readonly string $driver,
        public readonly string $queue,
        public readonly string $id,
        public readonly mixed $payload,
        public readonly array $headers = []
    ) {
    }
}
