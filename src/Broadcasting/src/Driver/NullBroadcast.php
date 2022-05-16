<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Spiral\Broadcasting\BroadcastInterface;

final class NullBroadcast implements BroadcastInterface
{
    public function publish(iterable|string|\Stringable $topics, iterable|string $messages): void
    {
        // Do nothing
    }
}
