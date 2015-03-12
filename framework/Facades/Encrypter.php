<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Log\LoggerInterface;
use Spiral\Core\Facade;
use Spiral\Components\Encrypter\Encrypter as EncrypterComponent;

/**
 * @method static EncrypterComponent setKey(string $key)
 * @method static EncrypterComponent setCipher(string $cipher)
 * @method static EncrypterComponent setMode(string $mode)
 * @method static EncrypterComponent restoreDefaults()
 * @method static string random(int $length, bool $passWeak = false, bool $base64 = true)
 * @method static string buildSignature(string $string, string $salt = null)
 * @method static string createIV()
 * @method static string encrypt(mixed $data, bool $urlSafe = true)
 * @method static mixed decrypt(string $packed)
 * @method static string addPKCS7(string $string)
 * @method static string removePKCS7(string $string)
 * @method static string getAlias()
 * @method static EncrypterComponent make(array $parameters = array())
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface logger()
 * @method static EncrypterComponent getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class Encrypter extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'encrypter';
}