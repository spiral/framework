<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Broadcasting\BroadcastInterface;

final class NullBroadcast implements BroadcastInterface
{
    public function publish($topics, $messages): void
    {
        // Do nothing
    }

    public function authorize(ServerRequestInterface $request): bool
    {
        return true;
    }
}
