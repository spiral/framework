<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\ScopeInterface;

final class NullTracer implements TracerInterface
{
    private ?array $context = null;

    public function __construct(
        private readonly ?ScopeInterface $scope = new Container(),
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
        ], static fn (): mixed => ContainerScope::getContainer()->invoke($callback));
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
