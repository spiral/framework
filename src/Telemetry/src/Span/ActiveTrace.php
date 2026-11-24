<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Span;

use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerInterface;

/**
 * The innermost running trace of the current fiber, for {@see \Spiral\Telemetry\CurrentSpan}.
 *
 * A fiber sees only the traces it runs itself: a fiber started inside a trace has none until it starts
 * its own. The callback is not moved into a fiber of its own to hand the trace down, as
 * {@see \Spiral\Core\ContainerScope::runScope()} does with the container: state other code keeps per fiber,
 * like the OpenTelemetry context, would not reach the callback.
 *
 * @internal
 */
final class ActiveTrace
{
    private static ?self $main = null;

    /** @var \WeakMap<\Fiber, self>|null */
    private static ?\WeakMap $fibers = null;

    private function __construct(
        public readonly TracerInterface $tracer,
        public readonly SpanInterface $span,
    ) {}

    public static function current(): ?self
    {
        $fiber = \Fiber::getCurrent();

        return $fiber === null ? self::$main : self::$fibers[$fiber] ?? null;
    }

    /**
     * Runs the callback as the innermost trace of the current fiber.
     *
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     * @throws \Throwable
     */
    public static function run(TracerInterface $tracer, SpanInterface $span, callable $callback): mixed
    {
        $previous = self::current();
        self::set(new self($tracer, $span));

        try {
            return $callback();
        } finally {
            self::set($previous);
        }
    }

    private static function set(?self $trace): void
    {
        $fiber = \Fiber::getCurrent();
        if ($fiber === null) {
            self::$main = $trace;
            return;
        }

        self::$fibers ??= new \WeakMap();
        if ($trace === null) {
            unset(self::$fibers[$fiber]);
            return;
        }

        self::$fibers[$fiber] = $trace;
    }
}
