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

    public function push(string $name, array $payload = [], OptionsInterface $options = null): string
    {
        return $this->core->callAction($name, 'push', [
            'payload' => $payload,
            'options' => $options,
        ]);
    }
}
