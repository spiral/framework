<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * A tracer that knows spans opened inside its traces by other means than {@see TracerInterface::trace()},
 * and gives {@see CurrentSpan} the innermost of them.
 */
interface CurrentSpanProviderInterface
{
    /**
     * Called only while a trace of this tracer is the innermost one running.
     *
     * @return SpanInterface|null The innermost span, or null for the span of that trace.
     */
    public function getCurrentSpan(): ?SpanInterface;
}
