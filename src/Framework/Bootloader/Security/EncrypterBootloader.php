<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;

final class EncrypterBootloader extends Bootloader
{
    protected const SINGLETONS = [
        EncryptionInterface::class => EncrypterFactory::class,
    ];

    protected const BINDINGS = [
        EncrypterInterface::class => Encrypter::class,
    ];

    public function init(ConfiguratorInterface $config, EnvironmentInterface $env): void
    {
        $config->setDefaults(EncrypterConfig::CONFIG, ['key' => $env->get('ENCRYPTER_KEY')]);
    }
}
