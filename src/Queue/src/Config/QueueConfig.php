<?php

declare(strict_types=1);

namespace Spiral\Queue\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface as LegacyInterceptor;
use Spiral\Core\InjectableConfig;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Serializer\SerializerInterface;

/**
 * @psalm-type TLegacyInterceptors = array<class-string<LegacyInterceptor>|LegacyInterceptor|Autowire>
 * @psalm-type TNewInterceptors = array<class-string<InterceptorInterface>|InterceptorInterface|Autowire>
 * @psalm-type TInterceptors = TNewInterceptors|TLegacyInterceptors
 */
final class QueueConfig extends InjectableConfig
{
    public const CONFIG = 'queue';

    protected array $config = [
        'default' => 'sync',
        'aliases' => [],
        'driverAliases' => [],
        'connections' => [],
        'registry' => [
            'handlers' => [],
            'serializers' => [],
        ],
        'interceptors' => [
            'push' => [],
            'consume' => [],
        ],
        'defaultSerializer' => null,
    ];

    /**
     * Get connection aliases
     */
    public function getAliases(): array
    {
        return $this->config['aliases'];
    }

    /**
     * Get consumer interceptors
     *
     * @return TInterceptors
     */
    public function getConsumeInterceptors(): array
    {
        return (array)($this->config['interceptors']['consume'] ?? []);
    }

    /**
     * Get pusher interceptors
     *
     * @return TInterceptors
     */
    public function getPushInterceptors(): array
    {
        return (array)($this->config['interceptors']['push'] ?? []);
    }

    /**
     * @return non-empty-string
     * @throws InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        if (!\is_string($this->config['default'])) {
            throw new InvalidArgumentException('Default queue connection config value must be a string');
        }

        return $this->config['default'];
    }

    /**
     * @return array<string, class-string>
     */
    public function getDriverAliases(): array
    {
        return (array)($this->config['driverAliases'] ?? []);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getConnections(?string $driver = null): array
    {
        $connections = $this->config['connections'] ?? [];

        if ($driver === null) {
            return $connections;
        }

        $driverAliases = $this->getDriverAliases();

        if (isset($driverAliases[$driver])) {
            if (!\is_string($this->config['driverAliases'][$driver])) {
                throw new InvalidArgumentException(
                    \sprintf('Driver alias for `%s` value must be a string', $driver)
                );
            }

            $driver = $driverAliases[$driver];
        }

        return \array_filter($connections, static function (array $connection) use ($driverAliases, $driver): bool {
            if (empty($connection['driver'])) {
                return false;
            }

            $connectionDriver = $driverAliases[$connection['driver']] ?? $connection['driver'];

            return $connectionDriver === $driver;
        });
    }

    /**
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function getConnection(string $name): array
    {
        $connections = $this->getConnections();

        if (!isset($connections[$name])) {
            throw new InvalidArgumentException(sprintf('Queue connection with given name `%s` is not defined.', $name));
        }

        if (!isset($connections[$name]['driver'])) {
            throw new InvalidArgumentException(\sprintf('Driver for queue connection `%s` is not defined.', $name));
        }

        $connection = $connections[$name];
        $driver = $connection['driver'];

        if (!\is_string($driver)) {
            throw new InvalidArgumentException(
                \sprintf('Driver for queue connection `%s` value must be a string', $name)
            );
        }

        if (isset($this->config['driverAliases'][$driver])) {
            $connection['driver'] = $this->config['driverAliases'][$driver];
        }

        if (!\is_string($connection['driver'])) {
            throw new InvalidArgumentException(
                \sprintf('Driver alias for queue connection `%s` value must be a string', $name)
            );
        }

        return $connection;
    }

    /**
     * @return array<string, class-string>
     */
    public function getRegistryHandlers(): array
    {
        return (array)($this->config['registry']['handlers'] ?? []);
    }

    public function getRegistrySerializers(): array
    {
        return (array)($this->config['registry']['serializers'] ?? []);
    }

    /**
     * @psalm-return SerializerInterface|class-string|Autowire|null
     */
    public function getDefaultSerializer(): SerializerInterface|string|Autowire|null
    {
        return $this->config['defaultSerializer'] ?? null;
    }
}
