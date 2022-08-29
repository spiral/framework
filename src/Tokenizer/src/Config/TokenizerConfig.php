<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Config;

use Spiral\Core\InjectableConfig;

/**
 * Tokenizer component configuration.
 *
 * @psalm-type Scope = array{
 *     "directories": array<array-key, non-empty-string>,
 *     "exclude": array<array-key, non-empty-string>
 * }
 */
final class TokenizerConfig extends InjectableConfig
{
    public const CONFIG = 'tokenizer';

    /**
     * @var array{
     *     "directories": array<array-key, non-empty-string>,
     *     "exclude": array<array-key, non-empty-string>,
     *     "scopes": array<non-empty-string, Scope>
     * }
     */
    protected array $config = [
        'directories' => [],
        'exclude' => [],
        'scopes' => [],
    ];

    public function getDirectories(): array
    {
        return $this->config['directories'] ?? [\getcwd()];
    }

    public function getExcludes(): array
    {
        return $this->config['exclude'] ?? ['vendor', 'tests'];
    }

    /**
     * @return Scope
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
