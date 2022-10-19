<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

final class NullTracer implements TracerInterface
{
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
        $span->setAttributes($attributes);

        return $this->scope->runScope([
            SpanInterface::class => $span,
        ], static fn (InvokerInterface $invoker): mixed => $invoker->invoke($callback));
    }

    public function getContext(): array
    {
        return [];
    }
}
