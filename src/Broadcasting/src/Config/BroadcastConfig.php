<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Config;

use Spiral\Broadcasting\Exception\InvalidArgumentException;
use Spiral\Core\InjectableConfig;

final class BroadcastConfig extends InjectableConfig
{
    public const CONFIG = 'broadcasting';

    protected array $config = [
        'authorize' => [
            'path' => null,
            'topics' => [],
        ],
        'default' => 'null',
        'aliases' => [],
        'connections' => [],
        'driverAliases' => [],
    ];

    /**
     * Get registerer broadcast topics.
     *
     * @return array<string, callable>
     */
    public function getTopics(): array
    {
        return (array)($this->config['authorize']['topics'] ?? []);
    }

    /**
     * Get authorization path for broadcasting topics.
     */
    public function getAuthorizationPath(): ?string
    {
        return $this->config['authorize']['path'] ?? null;
    }

    /**
     * Get broadcast driver aliases
     */
    public function getAliases(): array
    {
        return (array)($this->config['aliases'] ?? []);
    }

    /**
     * Get default broadcast connection
     */
    public function getDefaultConnection(): string
    {
        if (empty($this->config['default'])) {
            throw new InvalidArgumentException('Default broadcast connection is not defined.');
        }

        if (!\is_string($this->config['default'])) {
            throw new InvalidArgumentException('Default broadcast connection config value must be a string');
        }

        return $this->config['default'];
    }

    public function getConnectionConfig(string $name): array
    {
        if (!isset($this->config['connections'][$name])) {
            throw new InvalidArgumentException(
                \sprintf('Config for connection `%s` is not defined.', $name)
            );
        }

        $config = $this->config['connections'][$name];

        if (!isset($config['driver'])) {
            throw new InvalidArgumentException(
                \sprintf('Driver for `%s` connection is not defined.', $name)
            );
        }

        if (!\is_string($config['driver'])) {
            throw new InvalidArgumentException(
                \sprintf('Driver value for `%s` connection must be a string', $name)
            );
        }

        if (isset($this->config['driverAliases'][$config['driver']])) {
            $config['driver'] = $this->config['driverAliases'][$config['driver']];
        }

        return $config;
    }
}
