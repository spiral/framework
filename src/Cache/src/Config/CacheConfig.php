<?php

declare(strict_types=1);

namespace Spiral\Cache\Config;

use Spiral\Cache\Exception\InvalidArgumentException;
use Spiral\Core\InjectableConfig;

final class CacheConfig extends InjectableConfig
{
    public const CONFIG = 'cache';

    protected array $config = [
        'default' => 'array',
        'aliases' => [],
        'typeAliases' => [],
        'storages' => [],
    ];

    /**
     * Get cache storage aliases
     */
    public function getAliases(): array
    {
        return $this->config['aliases'];
    }

    /**
     * Get default cache storage
     */
    public function getDefaultStorage(): string
    {
        if (!\is_string($this->config['default'])) {
            throw new InvalidArgumentException('Default cache storage config value must be a string');
        }

        return $this->config['default'];
    }

    public function getStorageConfig(string $name): array
    {
        if (!isset($this->config['storages'][$name])) {
            throw new InvalidArgumentException(
                \sprintf('Config for storage `%s` is not defined.', $name)
            );
        }

        $config = $this->config['storages'][$name];

        if (!isset($config['type'])) {
            throw new InvalidArgumentException(
                \sprintf('Storage type for `%s` is not defined.', $name)
            );
        }

        if (!\is_string($config['type'])) {
            throw new InvalidArgumentException(
                \sprintf('Storage type value for `%s` must be a string', $name)
            );
        }

        if (isset($this->config['typeAliases'][$config['type']])) {
            $config['type'] = $this->config['typeAliases'][$config['type']];
        }

        return $config;
    }
}
