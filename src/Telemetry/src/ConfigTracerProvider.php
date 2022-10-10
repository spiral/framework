<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Telemetry\Config\TelemetryConfig;

final class ConfigTracerProvider implements TracerProviderInterface
{
    /** @var TracerInterface[] */
    private array $drivers = [];

    public function __construct(
        private readonly TelemetryConfig $config,
        private readonly FactoryInterface $factory
    ) {
    }

    public function getTracer(?string $name = null): TracerInterface
    {
        $name ??= $this->config->getDefaultDriver();

        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        return $this->drivers[$name] = $this->resolve($name);
    }

    private function resolve(string $name): TracerInterface
    {
        $config = $this->config->geDriverConfig($name);

        if ($config instanceof Autowire) {
            return $config->resolve($this->factory);
        }

        return $this->factory->make($config);
    }
}
