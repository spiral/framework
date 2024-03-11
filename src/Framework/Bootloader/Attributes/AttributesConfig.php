<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Attributes;

use Spiral\Core\InjectableConfig;

final class AttributesConfig extends InjectableConfig
{
    public const CONFIG = 'attributes';

    /**
     * @var array{
     *     annotations: array{support:bool},
     *     cache: array{storage: null|non-empty-string, enabled: bool},
     * }
     */
    protected array $config = [
        'annotations' => [
            'support' => true,
        ],
        'cache' => [
            'storage' => null,
            'enabled' => false,
        ],
    ];

    public function isAnnotationsReaderEnabled(): bool
    {
        return (bool)$this->config['annotations']['support'];
    }

    public function isCacheEnabled(): bool
    {
        return (bool)($this->config['cache']['enabled'] ?? false);
    }

    /**
     * @return non-empty-string|null
     */
    public function getCacheStorage(): ?string
    {
        return $this->config['cache']['storage'] ?? null;
    }
}
