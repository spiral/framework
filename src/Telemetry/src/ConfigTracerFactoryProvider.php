<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Telemetry\Config\TelemetryConfig;

final class ConfigTracerFactoryProvider implements TracerFactoryProviderInterface
{
    /** @var TracerFactoryInterface[] */
    private array $drivers = [];

    public function __construct(
        private readonly TelemetryConfig $config,
        private readonly FactoryInterface $factory
    ) {
    }

    public function getTracerFactory(?string $name = null): TracerFactoryInterface
    {
        $name ??= $this->config->getDefaultDriver();

        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        return $this->drivers[$name] = $this->resolve($name);
    }

    private function resolve(string $name): TracerFactoryInterface
    {
        $config = $this->config->getDriverConfig($name);

        if ($config instanceof TracerFactoryInterface) {
            return $config;
        }

        if ($config instanceof Autowire) {
            return $config->resolve($this->factory);
        }

        return $this->factory->make($config);
    }
}
