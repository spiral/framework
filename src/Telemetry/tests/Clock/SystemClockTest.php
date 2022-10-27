<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Clock;

use PHPUnit\Framework\TestCase;
use Spiral\Telemetry\Clock\SystemClock;

final class SystemClockTest extends TestCase
{
    public function testNow(): void
    {
        $clock = new SystemClock();

        $this->assertIsInt($clock->now());
    }
}
