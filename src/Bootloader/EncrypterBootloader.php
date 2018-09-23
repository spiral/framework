<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;

class EncrypterBootloader extends Bootloader
{
    const BINDINGS = [
        EncryptionInterface::class => EncrypterFactory::class,
        EncrypterInterface::class  => Encrypter::class
    ];
}