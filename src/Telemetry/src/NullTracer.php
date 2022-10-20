<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
final class NullTracer extends AbstractTracer
{
    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null
    ): mixed {
        $span = new Span($name);
        $span->setAttributes($attributes);

        return $this->runScope($span, $callback);
    }

    public function getContext(): array
    {
        return [];
    }
}
