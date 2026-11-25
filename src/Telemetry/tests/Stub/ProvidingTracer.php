<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Stub;

use Spiral\Core\Container;
use Spiral\Telemetry\AbstractTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\CurrentSpanProviderInterface;

/**
 * @internal
 */
final class ProvidingTracer extends AbstractTracer implements CurrentSpanProviderInterface
{
    /**
     * @param \Closure(): ?SpanInterface $provide
     */
    public function __construct(
        private readonly \Closure $provide,
    ) {
        parent::__construct(new Container());
    }

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null,
    ): mixed {
        return $this->runScope(new Span($name), $callback);
    }

    public function getContext(): array
    {
        return [];
    }

    public function getCurrentSpan(): ?SpanInterface
    {
        return ($this->provide)();
    }
}
