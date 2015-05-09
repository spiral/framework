<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Http\Cookies\Cookie;
use Spiral\Components\Http\Cookies\CookieManager;
use Spiral\Core\Facade;

/**
 * @method static setEncrypter(Encrypter $encrypter)
 * @method static Encrypter getEncrypter()
 * @method static Cookie set(string $name, string $value = null, int $lifetime = 0, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = true)
 * @method static CookieManager add(Cookie $cookie)
 * @method static Cookie[] getScheduled()
 * @method static string getAlias()
 * @method static CookieManager make(array $parameters = array())
 * @method static CookieManager getInstance()
 */
class Cookies extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cookies';
}