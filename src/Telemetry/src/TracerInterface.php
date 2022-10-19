<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

interface TracerInterface
{
    /**
     * Get current tracer context
     */
    public function getContext(): array;

    /**
     * Trace a given callback
     *
     * @param non-empty-string $name
     * @param array<non-empty-string, mixed> $attributes
     * @param int|null $startTime Start time in nanoseconds.
     *
     * @throws \Throwable
     */
    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        bool $debug = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null
    ): mixed;
}
