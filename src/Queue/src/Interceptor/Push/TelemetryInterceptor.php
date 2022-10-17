<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

class TelemetryInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ?TracerInterface $tracer = new NullTracer(),
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $parameters['telemetry'] = $this->tracer->getContext();

        return $core->callAction($controller, $action, $parameters);
    }
}
