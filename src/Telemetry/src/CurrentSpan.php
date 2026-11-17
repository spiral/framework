<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Telemetry\Span\ActiveTrace;
use Spiral\Telemetry\Span\NoopSpan;

/**
 * The innermost span the code runs in, for code that has no span passed to it:
 *
 *     CurrentSpan::get()->setAttribute('dict.org_types.count', \count($types));
 *
 * It is the span of the innermost running trace of a tracer based on {@see AbstractTracer}, scoped or not,
 * or the span the tracer of that trace gives as a {@see CurrentSpanProviderInterface}. Outside a trace it
 * is a {@see NoopSpan} that drops everything.
 */
final class CurrentSpan
{
    private function __construct() {}

    public static function get(): SpanInterface
    {
        $trace = ActiveTrace::current();
        if ($trace === null) {
            return NoopSpan::instance();
        }

        if ($trace->tracer instanceof CurrentSpanProviderInterface) {
            try {
                return $trace->tracer->getCurrentSpan() ?? $trace->span;
            } catch (\Throwable) {
                return $trace->span;
            }
        }

        return $trace->span;
    }
}
