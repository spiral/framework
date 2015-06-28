<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Facade;

/**
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
 */
class Input extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'input';
}