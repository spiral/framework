<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

final class NullTracer implements TracerInterface
{
    private ?array $context = null;

    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly ScopeInterface $scope,
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

        return $this->scope->runScope([
            SpanInterface::class => $span,
        ], fn (): mixed => $this->invoker->invoke($callback));
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
