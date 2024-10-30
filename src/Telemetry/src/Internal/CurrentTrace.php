<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Internal;

use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerInterface;

/**
 * @internal
 */
final class CurrentTrace
{
    public function __construct(
        public readonly TracerInterface $tracer,
        public readonly ?SpanInterface $span,
    ) {
    }
}
