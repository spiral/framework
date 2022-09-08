<?php

declare(strict_types=1);

namespace Spiral\Encrypter\Config;

use Spiral\Core\InjectableConfig;

/**
 * Encrypter configuration.
 */
final class EncrypterConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    public const CONFIG = 'encrypter';

    protected array $config = [
        'key' => '',
    ];

    /**
     * Encryption key in BASE64 format.
     */
    public function getKey(): string
    {
        return $this->config['key'] ?? '';
    }
}
