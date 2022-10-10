<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;

final class Handler
{
    public function __construct(
        private readonly TracerFactoryInterface $tracerFactory,
        private readonly ScopeInterface $scope,
        private readonly CoreInterface $core
    ) {
    }

    public function handle(
        string $name,
        string $driver,
        string $queue,
        string $id,
        array $payload,
        array $context = []
    ): mixed {
        $tracer = $this->tracerFactory->fromContext($context['headers'] ?? []);

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
                    'context' => $context,
                ]),
                scoped: true,
                traceKind: TraceKind::CONSUMER
            ),
        );
    }
}
