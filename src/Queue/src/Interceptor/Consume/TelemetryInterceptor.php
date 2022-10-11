<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;

class TelemetryInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly TracerFactoryInterface $tracerFactory,
        private readonly ScopeInterface $scope,
        private readonly TracerInterface $tracer,
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $ctx = (array)($parameters['context']['headers'] ?? []);

        $tracer = $this->tracerFactory->fromContext($ctx);

        return $this->scope->runScope([
            TracerInterface::class => $tracer,
        ], fn (): mixed => $tracer->trace('telemetry', fn () => $core->callAction($controller, $action, $parameters), [
            'controller' => $controller,
            'action' => $action,
        ]));
    }
}
