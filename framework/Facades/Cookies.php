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
use Spiral\Components\Http\Cookies\CookieInterface;
use Spiral\Components\Http\Cookies\CookieStore;
use Spiral\Core\Facade;

/**
 * @method static setEncrypter(Encrypter $encrypter)
 * @method static Encrypter getEncrypter()
 * @method static Cookie set(string $name, string $value = null, int $lifetime = 0, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = true)
 * @method static CookieStore add(CookieInterface $cookie)
 * @method static CookieInterface[] getScheduled()
 * @method static string getAlias()
 * @method static CookieStore make(array $parameters = array())
 * @method static CookieStore getInstance()
 */
class Cookies extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cookies';
}