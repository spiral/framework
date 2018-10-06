<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Security;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;

class EncrypterBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        EncryptionInterface::class => EncrypterFactory::class,
        EncrypterInterface::class  => Encrypter::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param EnvironmentInterface  $environment
     */
    public function boot(ConfiguratorInterface $configurator, EnvironmentInterface $environment)
    {
        $configurator->setDefaults('encrypter', [
            'key' => $environment->get('ENCRYPTER_KEY')
        ]);
    }
}