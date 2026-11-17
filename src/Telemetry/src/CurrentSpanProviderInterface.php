<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * Gives {@see CurrentSpan} spans opened by other means than {@see TracerInterface::trace()}.
 *
 * A tracer implementing it is asked while its own trace is the innermost one; a provider set with
 * {@see CurrentSpan::setProvider()} is asked outside any trace.
 */
interface CurrentSpanProviderInterface
{
    /**
     * @return SpanInterface|null The innermost span the provider knows of, or null to leave it to
     *         {@see CurrentSpan}: the span of the trace, or a no-op span outside a trace.
     */
    public function getCurrentSpan(): ?SpanInterface;
}
