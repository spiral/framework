<?php

declare(strict_types=1);

namespace Framework\Bootloader\Telemetry;

use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Telemetry\Clock\SystemClock;
use Spiral\Telemetry\ClockInterface;
use Spiral\Telemetry\Config\TelemetryConfig;
use Spiral\Telemetry\ConfigTracerFactoryProvider;
use Spiral\Telemetry\LogTracerFactory;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Telemetry\TracerFactoryProviderInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class TelemetryBootloaderTest extends BaseTestCase
{
    public function testTracerFactoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            TracerFactoryInterface::class,
            NullTracerFactory::class,
        );
    }

    public function testTracerProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            TracerFactoryProviderInterface::class,
            ConfigTracerFactoryProvider::class,
        );
    }

    public function testClockInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            ClockInterface::class,
            SystemClock::class,
        );
    }

    public function testTracerInterfaceBinding(): void
    {
        $this->assertContainerBound(
            TracerInterface::class,
            NullTracer::class,
        );
    }

    public function testConfig(): void
    {
        self::assertSame([
            'default' => 'null',
            'drivers' => [
                'null' => NullTracerFactory::class,
                'log' => LogTracerFactory::class,
            ],
        ], $this->getConfig(TelemetryConfig::CONFIG));
    }

    public function testRegisterTracer(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TelemetryConfig::CONFIG, ['drivers' => []]);

        $bootloader = new TelemetryBootloader($configs);
        $bootloader->registerTracer(
            'foo',
            $driver = $this->createMock(TracerFactoryInterface::class),
        );

        $bootloader->registerTracer(
            'foo1',
            $driver1 = 'bar',
        );

        $bootloader->registerTracer(
            'foo2',
            $driver2 = new Autowire('bar'),
        );

        self::assertSame([
            'foo' => $driver,
            'foo1' => $driver1,
            'foo2' => $driver2,
        ], $configs->getConfig(TelemetryConfig::CONFIG)['drivers']);
    }
}
