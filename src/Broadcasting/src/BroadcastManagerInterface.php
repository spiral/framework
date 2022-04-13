<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

interface BroadcastManagerInterface
{
    public function driver(?string $name = null): BroadcastInterface;
}
