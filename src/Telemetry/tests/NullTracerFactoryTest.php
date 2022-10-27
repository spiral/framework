<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\NullTracerFactory;

final class NullTracerFactoryTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testMake(): void
    {
        $factory = new NullTracerFactory(
            $scope = m::mock(ScopeInterface::class)
        );

        $scope->shouldReceive('runScope')->once();

        $this->assertInstanceOf(NullTracer::class, $tracer = $factory->make());

        $tracer->trace('foo', fn() => 'hello');
    }
}
