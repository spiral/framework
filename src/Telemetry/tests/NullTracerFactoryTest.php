<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\NullTracerFactory;

final class NullTracerFactoryTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    public function testMake(): void
    {
        $factory = new NullTracerFactory(m::mock(ScopeInterface::class));

        self::assertInstanceOf(NullTracer::class, $factory->make());
    }
}
