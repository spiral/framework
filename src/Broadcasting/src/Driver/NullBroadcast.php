<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Spiral\Broadcasting\BroadcastInterface;

final class NullBroadcast implements BroadcastInterface
{
    public function publish($topics, $messages): void
    {
        // Do nothing
    }
}
