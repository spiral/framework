<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;

final class Handler
{
    private readonly TracerFactoryInterface $tracerFactory;

    public function __construct(
        private readonly CoreInterface $core,
        private readonly ?ScopeInterface $scope = new Container(),
        ?TracerFactoryInterface $tracerFactory = null
    ) {
        $this->tracerFactory = $tracerFactory ?? new NullTracerFactory($this->scope);
    }

    public function handle(
        string $name,
        string $driver,
        string $queue,
        string $id,
        array $payload,
        array $headers = []
    ): mixed {
        $tracer = $this->tracerFactory->make($headers);

        return $this->scope->runScope(
            [
                TracerInterface::class => $tracer,
            ],
            fn (): mixed => $tracer->trace(
                name: \sprintf('Job handling [%s:%s]', $name, $id),
                callback: fn (): mixed => $this->core->callAction($name, 'handle', [
                    'driver' => $driver,
                    'queue' => $queue,
                    'id' => $id,
                    'payload' => $payload,
                    'headers' => $headers,
                ]),
                attributes: compact('driver', 'queue', 'id', 'headers'),
                scoped: true,
                traceKind: TraceKind::CONSUMER
            )
        );
    }
}
