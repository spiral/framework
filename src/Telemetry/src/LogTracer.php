<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Psr\Log\LoggerInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Logger\LogsInterface;

final class LogTracer implements TracerInterface
{
    private ?array $context = null;
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly ScopeInterface $scope,
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
        ?TraceKind $traceKind = null
    ): mixed {
        $span = new Span($name);

        $startTime = \microtime(true);

        $result = $this->scope->runScope([
            SpanInterface::class => $span,
        ], fn (): mixed => $this->invoker->invoke($callback));

        $elapsedSecs = microtime(true) - $startTime;

        $this->logger->debug(\sprintf('Trace [%s] - [%01.4f sec.]', $name, $elapsedSecs), [
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus(),
            'context' => $this->getContext(),
            'scoped' => $scoped,
            'trace_kind' => $traceKind,
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
