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
     * @psalm-var array{
     *     "directories": TDirectories,
     *     "exclude": TDirectories,
     *     "scopes": array<non-empty-string, TScope>
     * }
     * @var array
     */
    protected array $config = [
        'debug' => false,
        'directories' => [],
        'exclude' => [],
        'scopes' => [],
    ];

    public function isDebug(): bool
    {
        return (bool) ($this->config['debug'] ?? false);
    }

    /**
     * @return TDirectories
     */
    public function getDirectories(): array
    {
        return $this->config['directories'] ?? [(string) \getcwd()];
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
}
