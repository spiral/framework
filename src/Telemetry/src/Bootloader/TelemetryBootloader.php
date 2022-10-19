<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Telemetry\Clock\SystemClock;
use Spiral\Telemetry\ClockInterface;
use Spiral\Telemetry\Config\TelemetryConfig;
use Spiral\Telemetry\ConfigTracerFactoryProvider;
use Spiral\Telemetry\Exception\TracerException;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\LogTracerFactory;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Telemetry\TracerFactoryProviderInterface;

final class TelemetryBootloader extends Bootloader
{
    protected const SINGLETONS = [
        TracerFactoryInterface::class => [self::class, 'initFactory'],
        TracerFactoryProviderInterface::class => ConfigTracerFactoryProvider::class,
        ClockInterface::class => SystemClock::class,
    ];

    protected const BINDINGS = [
        TracerInterface::class => [self::class, 'getTracer'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->initConfig($env);
    }

    /**
     * @param class-string<TracerFactoryInterface>|TracerFactoryInterface|Autowire $driver
     */
    public function registerTracer(string $name, string|TracerFactoryInterface|Autowire $driver): void
    {
        $this->config->modify(
            TelemetryConfig::CONFIG,
            new Append('drivers', $name, $driver)
        );
    }

    /**
     * @throws TracerException
     */
    public function initFactory(
        TracerFactoryProviderInterface $tracerProvider
    ): TracerFactoryInterface {
        return $tracerProvider->getTracerFactory();
    }

    /**
     * @throws TracerException
     */
    public function getTracer(
        TracerFactoryInterface $tracerFactory
    ): TracerInterface {
        return $tracerFactory->make();
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            TelemetryConfig::CONFIG,
            [
                'default' => $env->get('TELEMETRY_DRIVER', 'null'),
                'drivers' => [
                    'null' => NullTracerFactory::class,
                    'log' => LogTracerFactory::class,
                ],
            ]
        );
    }
}
