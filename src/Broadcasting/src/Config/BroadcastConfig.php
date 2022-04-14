<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Config;

use Spiral\Broadcasting\Exception\InvalidArgumentException;
use Spiral\Core\InjectableConfig;

final class BroadcastConfig extends InjectableConfig
{
    public const CONFIG = 'broadcasting';

    protected $config = [
        'authorize' => [
            'path' => null,
            'topics' => [],
        ],
        'default' => 'null',
        'aliases' => [],
        'connections' => [],
        'driverAliases' => [],
    ];
    private array $patterns = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $topics = (array)($config['authorize']['topics'] ?? []);
        foreach ($topics as $topic => $callback) {
            $this->patterns[$this->compilePattern($topic)] = $callback;
        }
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
        if (!isset($this->config['default']) || empty($this->config['default'])) {
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
                sprintf('Config for connection `%s` is not defined.', $name)
            );
        }

        $config = $this->config['connections'][$name];

        if (!isset($config['driver'])) {
            throw new InvalidArgumentException(
                sprintf('Driver for `%s` connection is not defined.', $name)
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

    public function findTopicCallback(string $topic, array &$matches): ?callable
    {
        foreach ($this->patterns as $pattern => $callback) {
            if (preg_match($pattern, $topic, $matches)) {
                return $callback;
            }
        }

        return null;
    }

    private function compilePattern(string $topic): string
    {
        $replaces = [];
        if (preg_match_all('/\{(\w+):?(.*?)?\}/', $topic, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);
            foreach ($variables as $key => $_) {
                $replaces['{' . $key . '}'] = '(?P<' . $key . '>[^\/\.]+)';
            }
        }

        return '/^' . strtr($topic, $replaces + ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.']) . '$/iu';
    }
}
