<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerFactory;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;

final class Handler
{
    public function __construct(
        private readonly CoreInterface $core,
        private readonly ?ScopeInterface $scope = new Container(),
        private readonly ?TracerFactoryInterface $tracerFactory = new TracerFactory(),
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
        $tracer = $this->tracerFactory->fromContext($headers);

        return $this->scope->runScope(
            [
                TracerInterface::class => $tracer,
            ],
            fn (): mixed => $tracer->trace(
                name: 'queue.handler',
                callback: fn (): mixed => $this->core->callAction($name, 'handle', [
                    'driver' => $driver,
                    'queue' => $queue,
                    'id' => $id,
                    'payload' => $payload,
                    'headers' => $headers,
                ]),
                scoped: true,
                traceKind: TraceKind::CONSUMER
            ),
        );
    }
}
