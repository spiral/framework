<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * A tracer that keeps state per fiber, like the OpenTelemetry context, and hands it into the fibers its
 * traces run in: inside a fiber, the callback of a trace runs in a fiber of its own, and so does a
 * container scope the trace opens.
 */
interface FiberAwareTracerInterface extends TracerInterface
{
    /**
     * Called inside a fiber, before a callback of a trace may be moved into a new fiber.
     *
     * @template TReturn
     * @param callable(mixed...): TReturn $callback
     * @return callable(mixed...): TReturn The callback that sets up the state taken from the current fiber,
     *         then calls the given one with the same arguments. It may run in the current fiber as well.
     */
    public function bindToFiber(callable $callback): callable;
}
