<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\RouteInterface;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;

final class RouterBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class
    ];

    protected const SINGLETONS = [
        CoreInterface::class           => Core::class,
        RouterInterface::class         => [self::class, 'router'],
        RouteInterface::class          => [self::class, 'route'],
        RequestHandlerInterface::class => RouterInterface::class,
    ];

    /**
     * @param HttpConfig         $config
     * @param UriHandler         $uriHandler
     * @param ContainerInterface $container
     * @return RouterInterface
     */
    private function router(
        HttpConfig $config,
        UriHandler $uriHandler,
        ContainerInterface $container
    ): RouterInterface {
        return new Router($config->getBasePath(), $uriHandler, $container);
    }

    /**
     * @param ServerRequestInterface $request
     * @return RouteInterface
     */
    private function route(ServerRequestInterface $request): RouteInterface
    {
        $route = $request->getAttribute(Router::ROUTE_ATTRIBUTE, null);
        if ($route === null) {
            throw new ScopeException('Unable to resolve Route, invalid request scope');
        }

        return $route;
    }
}