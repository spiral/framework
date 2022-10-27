<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Telemetry\Exception\InvalidArgumentException;
use Spiral\Telemetry\TracerFactoryInterface;

final class TelemetryConfig extends InjectableConfig
{
    public const CONFIG = 'telemetry';

    protected array $config = [
        'default' => 'null',
        'drivers' => [],
    ];

    /**
     * Get default trace driver
     *
     * @throws InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        if (!\is_string($this->config['default'])) {
            throw new InvalidArgumentException('Default trace driver config value must be a string');
        }

        return $this->config['default'];
    }

    /**
     * @param non-empty-string $name
     * @return class-string<TracerFactoryInterface>|Autowire|TracerFactoryInterface
     * @throws InvalidArgumentException
     */
    public function getDriverConfig(string $name): string|Autowire|TracerFactoryInterface
    {
        if (!isset($this->config['drivers'][$name])) {
            throw new InvalidArgumentException(
                \sprintf('Config for telemetry driver `%s` is not defined.', $name)
            );
        }

        $driver = $this->config['drivers'][$name];

        if ($driver instanceof TracerFactoryInterface) {
            return $driver;
        }

        if (!\is_string($driver) && !$driver instanceof Autowire) {
            throw new InvalidArgumentException(
                \sprintf('Trace type value for `%s` must be a string or %s', $name, Autowire::class)
            );
        }

        return $driver;
    }
}
