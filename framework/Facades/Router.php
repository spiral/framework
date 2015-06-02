<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Http\Response\Redirect;
use Spiral\Components\Http\Router\RouteInterface;
use Spiral\Core\Facade;
use Spiral\Components\Http\Router\Route as HttpRouter;

/**
 * Attention, this facade will not work outside Router scope!
 *
 * @method static RouteInterface[] getRoutes()
 * @method static RouteInterface getRoute($route)
 * @method static RouteInterface|null activeRoute()
 * @method static string url(string $route, array $parameters = array())
 * @method static Redirect redirect(string $route, array $parameters = array())
 * @method static HttpRouter getAlias()
 * @method static HttpRouter make(array $parameters = array())
 */
class Router extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'router';
}