<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /**
     * @var array
     */
    protected $config = [
        'key' => ''
    ];

    /**
     * Encryption key in BASE64 format.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->config['key'] ?? '';
    }
}
