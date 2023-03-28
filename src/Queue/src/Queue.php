<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\CoreInterface;

/**
 * This class is used to push jobs into the queue and pass them through the interceptor chain
 * {@see \Spiral\Queue\Interceptor\Push\Core}. After that the job is pushed into the queue using the connection.
 *
 * @internal Queue is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Queue
 */
final class Queue implements QueueInterface
{
    public function __construct(
        private readonly CoreInterface $core,
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
