<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\CoreInterface;

final class Queue implements QueueInterface
{
    public function __construct(
        private readonly CoreInterface $core
    ) {
    }

    public function push(string $name, mixed $payload = [], mixed $options = null): string
    {
        return $this->core->callAction($name, 'push', [
            'payload' => $payload,
            'options' => $options,
        ]);
    }
}
