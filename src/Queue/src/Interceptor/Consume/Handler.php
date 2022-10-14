<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterface;

final class Handler
{
    public function __construct(
        private readonly CoreInterface $core
    ) {
    }

    public function handle(
        string $name,
        string $driver,
        string $queue,
        string $id,
        array $payload,
        array $headers = []
    ): mixed {
        return $this->core->callAction($name, 'handle', [
            'driver' => $driver,
            'queue' => $queue,
            'id' => $id,
            'payload' => $payload,
            'headers' => $headers,
        ]);
    }
}
