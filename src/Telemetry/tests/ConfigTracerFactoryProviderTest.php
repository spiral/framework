<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Core\FactoryInterface;
use Spiral\Telemetry\Config\TelemetryConfig;
use Spiral\Telemetry\ConfigTracerFactoryProvider;
use Spiral\Telemetry\Exception\InvalidArgumentException;
use Spiral\Telemetry\TracerFactoryInterface;

final class ConfigTracerFactoryProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetsTraceeFactory(): void
    {
        $provider = new ConfigTracerFactoryProvider(
            new TelemetryConfig(['drivers' => ['foo' => 'bar']]),
            $factory = \Mockery::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')
            ->once()
            ->with('bar')
            ->andReturn($f = \Mockery::mock(TracerFactoryInterface::class));

        $this->assertSame($f, $provider->getTracerFactory('foo'));
    }

    public function testGetsTraceeFactoryWithDefaultName(): void
    {
        $provider = new ConfigTracerFactoryProvider(
            new TelemetryConfig([
                'default' => 'foo',
                'drivers' => ['foo' => 'bar']
            ]),
            $factory = \Mockery::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')
            ->once()
            ->with('bar')
            ->andReturn($f = \Mockery::mock(TracerFactoryInterface::class));

        $this->assertSame($f, $provider->getTracerFactory());
    }

    public function testGetsTraceeFactoryWithNonExistName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Config for telemetry driver `bar` is not defined.');

        $provider = new ConfigTracerFactoryProvider(
            new TelemetryConfig([
                'default' => 'bar',
                'drivers' => ['foo' => 'bar']
            ]),
            $factory = \Mockery::mock(FactoryInterface::class)
        );

        $provider->getTracerFactory();
    }
}
