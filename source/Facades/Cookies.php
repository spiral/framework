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
 * Attention, this facade will not work outside CookieManager scope!
 */
class Cookies extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'cookies';
}