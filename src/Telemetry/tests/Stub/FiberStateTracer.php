<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Stub;

use Spiral\Core\Container;
use Spiral\Telemetry\AbstractTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\TraceKind;
use Spiral\Core\InvokerInterface;

/**
 * Keeps the name of the running trace per fiber, with no fallback to the parent fiber, as the
 * OpenTelemetry context does, and sets it up again in the fiber its callback ends up in, as the OTel
 * bridge does with its context.
 *
 * @internal
 */
final class FiberStateTracer extends AbstractTracer
{
    /** @var \WeakMap<\Fiber, string> */
    private \WeakMap $fibers;

    private ?string $main = null;

    public function __construct()
    {
        parent::__construct(new Container());
        $this->fibers = new \WeakMap();
    }

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null,
    ): mixed {
        return $this->runScope(
            new Span($name),
            function (InvokerInterface $invoker) use ($name, $callback): mixed {
                $previous = $this->state();
                $this->setState($name);

                try {
                    return $invoker->invoke($callback);
                } finally {
                    $this->setState($previous);
                }
            },
        );
    }

    public function getContext(): array
    {
        return [];
    }

    public function state(): ?string
    {
        $fiber = \Fiber::getCurrent();

        return $fiber === null ? $this->main : $this->fibers[$fiber] ?? null;
    }

    private function setState(?string $state): void
    {
        $fiber = \Fiber::getCurrent();
        $fiber === null ? $this->main = $state : $this->fibers[$fiber] = $state;
    }
}
