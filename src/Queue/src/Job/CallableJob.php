<?php

declare(strict_types=1);

namespace Spiral\Queue\Job;

use Spiral\Core\InvokerInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\HandlerInterface;

/**
 * @deprecated Will be removed in v4.0
 */
final class CallableJob implements HandlerInterface
{
    public function __construct(
        private readonly InvokerInterface $invoker
    ) {
    }

    public function handle(string $name, string $id, array $payload, array $headers = []): void
    {
        if (!isset($payload['callback'])) {
            throw new InvalidArgumentException('Payload `callback` key is required.');
        }

        if (!$payload['callback'] instanceof \Closure) {
            throw new InvalidArgumentException('Payload `callback` key value type should be a closure.');
        }

        $this->invoker->invoke(
            $payload['callback'],
            [
                'name' => $name,
                'id' => $id,
                'headers' => $headers,
            ]
        );
    }
}
