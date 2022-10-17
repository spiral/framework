<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Psr\Log\LoggerInterface;
use Spiral\Core\ContainerScope;
use Spiral\Core\ScopeInterface;
use Spiral\Logger\LogsInterface;

final class LogTracer implements TracerInterface
{
    private ?array $context = null;
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly ClockInterface $clock,
        LogsInterface $logs,
        string $channel = 'telemetry'
    ) {
        $this->logger = $logs->getLogger($channel);
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
        ], static fn (): mixed => ContainerScope::getContainer()->invoke($callback));

        $elapsed = $this->clock->now() - $startTime;

        $this->logger->debug(\sprintf('Trace [%s] - [%01.4f ms.]', $name, $elapsed / 1_000_000_000), [
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus(),
            'context' => $this->getContext(),
            'scoped' => $scoped,
            'trace_kind' => $traceKind,
            'elapsed' => $elapsed,
        ]);

        return $result;
    }

    public function withContext(mixed $context): self
    {
        $self = clone $this;
        $self->context = $context;

        return $self;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
