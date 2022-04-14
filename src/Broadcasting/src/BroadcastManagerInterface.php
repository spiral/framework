<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

interface BroadcastManagerInterface
{
    public function connection(?string $name = null): BroadcastInterface;
}
