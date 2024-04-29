<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface as InterceptorHandler;

/**
 * This class is used to push jobs into the queue and pass them through the interceptor chain
 * {@see \Spiral\Queue\Interceptor\Push\Core}. After that the job is pushed into the queue using the connection.
 *
 * @internal Queue is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Queue
 */
final class Queue implements QueueInterface
{
    private readonly bool $isLegacy;

    public function __construct(
        private readonly CoreInterface|InterceptorHandler $core,
    ) {
        $this->isLegacy = !$core instanceof HandlerInterface;
    }

    public function push(string $name, mixed $payload = [], mixed $options = null): string
    {
        $arguments = [
            'payload' => $payload,
            'options' => $options,
        ];

        return $this->isLegacy
            ? $this->core->callAction($name, 'push', $arguments)
            : $this->core->handle(new CallContext(Target::fromPair($name, 'push'), $arguments));
    }
}
