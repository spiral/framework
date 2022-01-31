<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface QueueConnectionProviderInterface
{
    public function getConnection(?string $name = null): QueueInterface;
}
