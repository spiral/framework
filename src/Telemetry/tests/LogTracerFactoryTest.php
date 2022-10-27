<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Telemetry\ClockInterface;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\LogTracerFactory;

final class LogTracerFactoryTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testMake(): void
    {
        $logs = m::mock(LogsInterface::class);

        $logs->shouldReceive('getLogger')->once()
            ->with('some-channel')
            ->andReturn($logger = m::mock(LoggerInterface::class));

        $factory = new LogTracerFactory(
            $scope = m::mock(ScopeInterface::class),
            $clock = m::mock(ClockInterface::class),
            $logs,
            'some-channel'
        );

        $clock->shouldReceive('now');
        $scope->shouldReceive('runScope')->once();
        $logger->shouldReceive('debug')->once();

        $this->assertInstanceOf(LogTracer::class, $tracer = $factory->make());

        $tracer->trace('foo', fn() => 'hello');
    }
}

