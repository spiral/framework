<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;

class RoutingBootloader extends Bootloader
{
    const SINGLETONS = [
        CoreInterface::class           => Core::class,
        RouterInterface::class         => [self::class, 'router'],
        RequestHandlerInterface::class => RouterInterface::class,
    ];

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     * @return RouterInterface
     */
    protected function router(HttpConfig $config, ContainerInterface $container): RouterInterface
    {
        return new Router($config->basePath(), $container);
    }
}