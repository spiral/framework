<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Predis\Response\ResponseInterface;
use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\Http\HttpDispatcher;
use Spiral\Components\Http\MiddlewareInterface;
use Spiral\Components\Http\Router\Route;
use Spiral\Components\Http\Router\RouteInterface;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Facade;


class Http extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'http';
}