<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Monolog;

use Mockery as m;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Telemetry\Monolog\TelemetryProcessor;
use Spiral\Telemetry\TracerInterface;

#[RunClassInSeparateProcess]
final class TelemetryProcessorTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testProcess(): void
    {
        $processor = new TelemetryProcessor(
            $container = m::mock(ContainerInterface::class)
        );

        $container->shouldReceive('get')
            ->once()
            ->with(TracerInterface::class)
            ->andReturn($tracer = m::mock(TracerInterface::class));

        $tracer->shouldReceive('getContext')->once()->andReturn(['foo' => 'bar']);

        $record = $processor->__invoke(['baz' => 'baf']);

        self::assertSame(['baz' => 'baf', 'extra' => ['telemetry' => ['foo' => 'bar']]], $record);
    }

    public function testProcessWithEmptyContext(): void
    {
        $processor = new TelemetryProcessor(
            $container = m::mock(ContainerInterface::class)
        );

        $container->shouldReceive('get')
            ->once()
            ->with(TracerInterface::class)
            ->andReturn($tracer = m::mock(TracerInterface::class));

        $tracer->shouldReceive('getContext')->once()->andReturn([]);

        $record = $processor->__invoke(['baz' => 'baf']);

        self::assertSame(['baz' => 'baf'], $record);
    }
}
