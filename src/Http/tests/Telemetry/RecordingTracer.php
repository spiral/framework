<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Telemetry;

use Spiral\Telemetry\Span;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerInterface;

/**
 * Runs the callback with a fresh span and records how the span ended.
 */
final class RecordingTracer implements TracerInterface
{
    /**
     * @var list<array{
     *     name: string,
     *     kind: TraceKind|null,
     *     scoped: bool,
     *     attributes: array<non-empty-string, mixed>,
     *     status: string|int|null
     * }>
     */
    public array $spans = [];

    /**
     * @param array<non-empty-string, string> $context
     */
    public function __construct(
        private readonly array $context,
    ) {}

    public function getContext(): array
    {
        return $this->context;
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
        $result = $callback($span);

        $this->spans[] = [
            'name' => $span->getName(),
            'kind' => $traceKind,
            'scoped' => $scoped,
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus()?->code,
        ];

        return $result;
    }
}
