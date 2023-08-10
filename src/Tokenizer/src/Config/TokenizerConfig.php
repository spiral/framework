<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Config;

use Spiral\Core\InjectableConfig;

/**
 * Tokenizer component configuration.
 *
 * @psalm-type TDirectories = array<array-key, string>
 *
 * @psalm-type TScope = array{
 *     "directories": TDirectories,
 *     "exclude": TDirectories
 * }
 */
final class TokenizerConfig extends InjectableConfig
{
    public const CONFIG = 'tokenizer';

    /**
     * @var array{
     *     cache: array{directory: null, enabled: bool},
     *     load: array{classes:bool, enums: bool, interfaces: bool},
     *     debug: bool,
     *     directories: TDirectories,
     *     exclude: TDirectories,
     *     scopes: array<non-empty-string, TScope>
     * }
     */
    protected array $config = [
        'cache' => [
            'directory' => null,
            'enabled' => false,
        ],
        'load' => [
            'classes' => true,
            'enums' => false,
            'interfaces' => false,
        ],
        'debug' => false,
        'directories' => [],
        'exclude' => [],
        'scopes' => [],
    ];

    public function isDebug(): bool
    {
        return (bool)($this->config['debug'] ?? false);
    }

    /**
     * @return TDirectories
     */
    public function getDirectories(): array
    {
        return $this->config['directories'] ?? [(string)\getcwd()];
    }

    /**
     * @return TDirectories
     */
    public function getExcludes(): array
    {
        return $this->config['exclude'] ?? ['vendor', 'tests'];
    }

    /**
     * @return TScope
     */
    public function getScope(string $scope): array
    {
        $directories = $this->config['scopes'][$scope]['directories'] ?? $this->getDirectories();
        $excludes = $this->config['scopes'][$scope]['exclude'] ?? $this->getExcludes();

        return [
            'directories' => $directories,
            'exclude' => $excludes,
        ];
    }

    public function getScopes(): array
    {
        return $this->config['scopes'] ?? [];
    }

    /**
     * Check if tokenizer listeners cache is enabled.
     */
    public function isCacheEnabled(): bool
    {
        return (bool)($this->config['cache']['enabled'] ?? false);
    }

    /**
     * Get tokenizer listeners cache directory.
     */
    public function getCacheDirectory(): ?string
    {
        $dir = $this->config['cache']['directory'] ?? null;
        \assert(\is_string($dir) || $dir === null, 'Invalid cache directory.');

        return $dir;
    }

    public function isLoadClassesEnabled(): bool
    {
        return (bool)($this->config['load']['classes'] ?? true);
    }

    public function isLoadEnumsEnabled(): bool
    {
        return (bool)($this->config['load']['enums'] ?? false);
    }

    public function isLoadInterfacesEnabled(): bool
    {
        return (bool)($this->config['load']['interfaces'] ?? false);
    }
}
