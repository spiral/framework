<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;

final class EncrypterBootloader extends Bootloader
{
    public const SINGLETONS = [
        EncryptionInterface::class => EncrypterFactory::class
    ];

    public const BINDINGS = [
        EncrypterInterface::class => Encrypter::class
    ];

    /**
     * @param ConfiguratorInterface $config
     * @param EnvironmentInterface  $env
     */
    public function boot(ConfiguratorInterface $config, EnvironmentInterface $env): void
    {
        $config->setDefaults('encrypter', ['key' => $env->get('ENCRYPTER_KEY')]);
    }
}
