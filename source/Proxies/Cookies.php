<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Spiral\Components\Encrypter\Encrypter as EncrypterComponent;
use Spiral\Components\Http\Cookies\Cookie;
use Spiral\Components\Http\Cookies\CookieManager;
use Spiral\Core\Container;
use Spiral\Core\StaticProxy;

/**
 * DO NOT use StaticProxies!
 * Attention, this facade will not work outside CookieManager scope!
 *
 * @method static void excludeCookie($name)
 * @method static void setEncrypter(EncrypterComponent $encrypter)
 * @method static Cookie create($name, $value = null, $lifetime = null, $path = null, $domain = null, $secure = null, $httpOnly = true)
 * @method static void setDomain($domain)
 * @method static string getDomain()
 * @method static Cookie set($name, $value = null, $lifetime = null, $path = null, $domain = null, $secure = null, $httpOnly = true)
 * @method static void delete($name)
 * @method static CookieManager add(Cookie $cookie)
 * @method static Cookie[] getScheduled()
 * @method static CookieManager make($parameters = [], Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class Cookies extends StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cookies';
}