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
 * Inside a trace of a tracer based on {@see AbstractTracer}, scoped or not, it is the span of the innermost
 * trace, or the span its tracer gives as a {@see CurrentSpanProviderInterface}. Outside a trace it is the
 * span of the provider set with {@see self::setProvider()}, or else a {@see NoopSpan} that drops everything.
 */
final class CurrentSpan
{
    private static ?CurrentSpanProviderInterface $provider = null;

    private function __construct() {}

    public static function get(): SpanInterface
    {
        $trace = ActiveTrace::current();
        if ($trace === null) {
            return self::ask(self::$provider) ?? NoopSpan::instance();
        }

        return $trace->tracer instanceof CurrentSpanProviderInterface
            ? self::ask($trace->tracer) ?? $trace->span
            : $trace->span;
    }

    /**
     * Sets the provider asked outside a trace, for spans opened by other means than a tracer, or unsets it
     * with null.
     */
    public static function setProvider(?CurrentSpanProviderInterface $provider): void
    {
        self::$provider = $provider;
    }

    private static function ask(?CurrentSpanProviderInterface $provider): ?SpanInterface
    {
        try {
            return $provider?->getCurrentSpan();
        } catch (\Throwable) {
            return null;
        }
    }
}
