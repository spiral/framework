<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Monolog;

use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Spiral\Telemetry\TracerInterface;

class TelemetryProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(array $record)
    {
        $tracer = $this->container->get(TracerInterface::class);
        \assert($tracer instanceof TracerInterface);

        $context = $tracer->getContext();

        if (!empty($context)) {
            $record['extra']['telemetry'] = $context;
        }

        return $record;
    }
}
