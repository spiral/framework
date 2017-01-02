<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Encrypter\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Encrypter configuration.
 */
class EncrypterConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'encrypter';

    /**
     * @var array
     */
    protected $config = [
        /*
         * Encryption key in base64 format.
         */
        'key' => ''
    ];

    /**
     * ANSI encryption key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->config['key'];
    }
}