<?php

declare(strict_types=1);

namespace Framework\Bootloader\Debug;

use Spiral\Debug\State;
use Spiral\Debug\StateCollector\EnvironmentCollector;
use Spiral\Debug\StateInterface;
use Spiral\Tests\Framework\BaseTest;

final class DebugBootloaderTest extends BaseTest
{
    public function testEnvironmentCollectorBinding(): void
    {
        $this->assertContainerBoundAsSingleton(EnvironmentCollector::class, EnvironmentCollector::class);
    }

    public function testStateInterfaceBinding(): void
    {
        $this->assertContainerBound(StateInterface::class, State::class);
    }
}
