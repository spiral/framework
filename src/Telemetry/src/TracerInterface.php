<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

interface TracerInterface
{
    public function withContext(?array $context): self;

    /**
     * Get current tracer context
     */
    public function getContext(): ?array;

    /**
     * Trace a given callback
     */
    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        bool $debug = false,
        ?TraceKind $traceKind = null
    ): mixed;
}
