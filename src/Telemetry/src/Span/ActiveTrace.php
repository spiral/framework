<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Span;

use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerInterface;

/**
 * The innermost running trace, for {@see \Spiral\Telemetry\CurrentSpan}.
 *
 * @internal
 */
final class ActiveTrace
{
    private static ?self $current = null;

    private function __construct(
        public readonly TracerInterface $tracer,
        public readonly SpanInterface $span,
    ) {}

    public static function current(): ?self
    {
        return self::$current;
    }

    /**
     * Runs the callback as the innermost trace.
     *
     * Inside a fiber the callback runs in a fiber of its own, and the trace is the current one only
     * while that fiber runs, as {@see \Spiral\Core\ContainerScope::runScope()} does with the container:
     * fibers switching around it see their own traces. A fiber started inside a trace gets it.
     *
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     * @throws \Throwable
     */
    public static function run(TracerInterface $tracer, SpanInterface $span, callable $callback): mixed
    {
        $previous = self::$current;
        $own = self::$current = new self($tracer, $span);

        try {
            if (\Fiber::getCurrent() === null) {
                return $callback();
            }

            $fiber = new \Fiber($callback);
            $value = $fiber->start();
            while (!$fiber->isTerminated()) {
                self::$current = $previous;
                try {
                    $resume = \Fiber::suspend($value);
                } catch (\Throwable $e) {
                    self::$current = $own;
                    $value = $fiber->throw($e);
                    continue;
                }

                self::$current = $own;
                $value = $fiber->resume($resume);
            }

            return $fiber->getReturn();
        } finally {
            // A suspended fiber destroyed by the garbage collector unwinds while another trace is current.
            if (self::$current === $own) {
                self::$current = $previous;
            }
        }
    }
}
