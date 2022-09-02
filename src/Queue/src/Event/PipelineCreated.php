<?php

declare(strict_types=1);

namespace Spiral\Queue\Event;

use Spiral\Queue\QueueInterface;

final class PipelineCreated
{
    public function __construct(
        public readonly string $name,
        public readonly QueueInterface $pipeline
    ) {
    }
}
