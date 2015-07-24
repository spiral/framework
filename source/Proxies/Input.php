<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Components\Http\Input\InputBag;
use Spiral\Components\Http\InputManager;
use Spiral\Core\Container;
use Spiral\Core\Proxy;

/**
 * @method static ServerRequestInterface getRequest()
 * @method static InputBag getBag($name)
 * @method static mixed header($name, $default = null, $implode = ',')
 * @method static mixed data($name, $default = null)
 * @method static mixed post($name, $default = null)
 * @method static mixed query($name, $default = null)
 * @method static mixed cookie($name, $default = null)
 * @method static UploadedFileInterface|null file($name, $default = null)
 * @method static mixed server($name, $default = null)
 * @method static mixed attribute($name, $default = null)
 * @method static UriInterface getUri()
 * @method static string getPath()
 * @method static string getMethod()
 * @method static bool isSecure()
 * @method static bool isAjax()
 * @method static string|null getRemoteAddress()
 * @method static InputManager make($parameters = [], Container $container = null)
 * @method static InputManager getInstance(Container $container = null)
 */
class Input extends Proxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'input';
}