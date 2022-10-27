<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Clock;

use Spiral\Telemetry\ClockInterface;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
final class SystemClock implements ClockInterface
{
    public function now(): int
    {
        return \hrtime(true);
    }
}
