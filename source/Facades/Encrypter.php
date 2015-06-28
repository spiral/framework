<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Core\Container;
use Spiral\Core\Facade;
use Spiral\Components\Encrypter\Encrypter as EncrypterComponent;

/**
 * @method static EncrypterComponent setKey($key)
 * @method static string getKey()
 * @method static EncrypterComponent setMethod($method)
 * @method static EncrypterComponent restoreDefaults()
 * @method static string random($length, $passWeak = false)
 * @method static string makeSignature($string, $salt = null)
 * @method static string encrypt($data)
 * @method static mixed decrypt($packed)
 * @method static EncrypterComponent make($parameters = [], Container $container = null)
 * @method static EncrypterComponent getInstance(Container $container = null)
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