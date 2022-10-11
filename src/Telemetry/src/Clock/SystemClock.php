<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Clock;

use Spiral\Telemetry\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): int
    {
        return \hrtime(true);
    }
}
