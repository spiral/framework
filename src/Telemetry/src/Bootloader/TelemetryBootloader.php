<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Telemetry\Config\TelemetryConfig;
use Spiral\Telemetry\ConfigTracerProvider;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerFactory;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Telemetry\TracerProviderInterface;

final class TelemetryBootloader extends Bootloader
{
    protected const SINGLETONS = [
        TracerFactoryInterface::class => TracerFactory::class,
        TracerProviderInterface::class => ConfigTracerProvider::class,
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
     * @param class-string<TracerInterface>|Autowire $driver
     */
    public function registerTracer(string $name, string|Autowire $driver): void
    {
        $this->config->modify(
            TelemetryConfig::CONFIG,
            new Append('drivers', $name, $driver)
        );
    }

    public function getTracer(
        TracerProviderInterface $tracerProvider
    ): TracerInterface {
        return $tracerProvider->getTracer();
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            TelemetryConfig::CONFIG,
            [
                'default' => $env->get('TELEMETRY_DRIVER', 'null'),
                'drivers' => [
                    'null' => NullTracer::class,
                    'log' => LogTracer::class
                ],
            ]
        );
    }
}
