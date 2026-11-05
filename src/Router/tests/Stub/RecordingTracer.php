<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Stub;

use Spiral\Telemetry\AbstractTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\TraceKind;

final class RecordingTracer extends AbstractTracer
{
    /** @var array<non-empty-string, bool> Span name => scoped flag */
    public array $traces = [];

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null,
    ): mixed {
        $this->traces[$name] = $scoped;

        return $this->runScope(new Span($name), $callback);
    }

    public function getContext(): array
    {
        return [];
    }
}
