<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
interface ClockInterface
{
    /**
     * @return int Current time in nanoseconds
     */
    public function now(): int;
}
