<?php

declare(strict_types=1);

namespace Spiral\Tests\Events;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Events\ListenerProcessorRegistry;
use Spiral\Events\Processor\ProcessorInterface;

final class ListenerProcessorRegistryTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testProcess(): void
    {
        $processor1 = m::mock(ProcessorInterface::class);
        $processor1->shouldReceive('process')->once();
        $processor2 = m::mock(ProcessorInterface::class);
        $processor2->shouldReceive('process')->once();

        $registry = new ListenerProcessorRegistry();

        $registry->addProcessor($processor1);
        $registry->addProcessor($processor2);

        $registry->process();
    }
}
