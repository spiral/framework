<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Telemetry;

use Spiral\Telemetry\Span;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerInterface;

/**
 * Runs the callback with a fresh span and records how the span ended, including a span whose callback threw.
 *
 * The context names the running spans from the outermost one, e.g. `request/GET`, so a test can tell which
 * span it was taken in.
 */
final class RecordingTracer implements TracerInterface
{
    /**
     * @var list<array{
     *     name: string,
     *     kind: TraceKind|null,
     *     scoped: bool,
     *     attributes: array<non-empty-string, mixed>,
     *     status: string|int|null,
     *     description: string|null
     * }>
     */
    public array $spans = [];

    /** @var list<non-empty-string> */
    private array $running = [];

    public function getContext(): array
    {
        return $this->running === [] ? [] : ['traceparent' => \implode('/', $this->running)];
    }

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null,
    ): mixed {
        $span = new Span($name, $attributes);
        $this->running[] = $name;

        try {
            return $callback($span);
        } finally {
            \array_pop($this->running);
            $this->spans[] = [
                'name' => $span->getName(),
                'kind' => $traceKind,
                'scoped' => $scoped,
                'attributes' => $span->getAttributes(),
                'status' => $span->getStatus()?->code,
                'description' => $span->getStatus()?->description,
            ];
        }
    }
}
