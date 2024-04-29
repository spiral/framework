<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerFactoryInterface;

/**
 * Handler is used to invoke pass incoming job through the interceptor chain and invoke the job handler after that.
 * {@see \Spiral\Queue\Interceptor\Consume\Core}.
 */
final class Handler
{
    private readonly TracerFactoryInterface $tracerFactory;
    private readonly bool $isLegacy;

    public function __construct(
        private readonly HandlerInterface|CoreInterface $core,
        ?TracerFactoryInterface $tracerFactory = null,
    ) {
        $this->tracerFactory = $tracerFactory ?? new NullTracerFactory(new Container());
        $this->isLegacy = !$core instanceof HandlerInterface;
    }

    public function handle(
        string $name,
        string $driver,
        string $queue,
        string $id,
        mixed $payload,
        array $headers = [],
    ): mixed {
        $tracer = $this->tracerFactory->make($headers);

        $arguments = [
            'driver' => $driver,
            'queue' => $queue,
            'id' => $id,
            'payload' => $payload,
            'headers' => $headers,
        ];

        return $tracer->trace(
            name: \sprintf('Job handling [%s:%s]', $name, $id),
            callback: $this->isLegacy
                ? fn (): mixed => $this->core->callAction($name, 'handle', $arguments)
                : fn (): mixed => $this->core->handle(new CallContext(Target::fromPair($name, 'handle'), $arguments)),
            attributes: [
                'queue.driver' => $driver,
                'queue.name' => $queue,
                'queue.id' => $id,
                'queue.headers' => $headers,
            ],
            scoped: true,
            traceKind: TraceKind::CONSUMER,
        );
    }
}
