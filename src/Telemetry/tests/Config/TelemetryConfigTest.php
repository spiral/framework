<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Config;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container\Autowire;
use Spiral\Telemetry\Config\TelemetryConfig;
use Spiral\Telemetry\TracerFactoryInterface;

final class TelemetryConfigTest extends TestCase
{
    public function testGetsDefaultDriver(): void
    {
        $config = new TelemetryConfig(['default' => 'foo']);

        self::assertSame('foo', $config->getDefaultDriver());
    }

    public function testGetsDriverConfigAsString(): void
    {
        $config = new TelemetryConfig(['drivers' => [
            'foo' => 'bar',
        ]]);

        self::assertSame('bar', $config->getDriverConfig('foo'));
    }

    public function testGetsDriverConfigAsAutowire(): void
    {
        $config = new TelemetryConfig(['drivers' => [
            'foo' => $driver = new Autowire('bar'),
        ]]);

        self::assertSame($driver, $config->getDriverConfig('foo'));
    }

    public function testGetsDriverConfigAsObject(): void
    {
        $config = new TelemetryConfig(['drivers' => [
            'foo' => $driver = m::mock(TracerFactoryInterface::class),
        ]]);

        self::assertSame($driver, $config->getDriverConfig('foo'));
    }
}
