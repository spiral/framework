<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Psr\Log\LoggerInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

final class LogTracer implements TracerInterface
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger
    ) {
    }

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        bool $debug = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null
    ): mixed {
        $span = new Span($name);

        $startTime ??= $this->clock->now();

        $result = $this->scope->runScope([
            SpanInterface::class => $span,
        ], static fn (InvokerInterface $invoker): mixed => $invoker->invoke($callback));

        $elapsed = $this->clock->now() - $startTime;

        $this->logger->debug(\sprintf('Trace [%s] - [%01.4f ms.]', $name, $elapsed / 1_000_000_000), [
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus(),
            'scoped' => $scoped,
            'trace_kind' => $traceKind,
            'elapsed' => $elapsed,
        ]);

        return $result;
    }

    public function getContext(): array
    {
        return [];
    }
}
