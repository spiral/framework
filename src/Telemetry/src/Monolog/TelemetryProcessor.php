<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Spiral\Telemetry\TracerInterface;

final class TelemetryProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(LogRecord|array $record): array
    {
        if ($record instanceof LogRecord) {
            $record = $record->toArray();
        }

        $tracer = $this->container->get(TracerInterface::class);
        \assert($tracer instanceof TracerInterface);

        $context = $tracer->getContext();

        if (!empty($context)) {
            $record['extra']['telemetry'] = $context;
        }

        return $record;
    }
}
