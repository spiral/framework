<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\LoaderRegistryInterface;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\RouteInterface;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;

final class RouterBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
    ];

    protected const SINGLETONS = [
        CoreInterface::class           => Core::class,
        RouterInterface::class         => [self::class, 'router'],
        RouteInterface::class          => [self::class, 'route'],
        RequestHandlerInterface::class => RouterInterface::class,
        LoaderInterface::class         => DelegatingLoader::class,
        LoaderRegistryInterface::class => [self::class, 'initRegistry']
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function router(
        UriHandler $uriHandler,
        ContainerInterface $container
    ): RouterInterface {
        return new Router($this->config->getConfig(HttpConfig::CONFIG)['basePath'], $uriHandler, $container);
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function route(ServerRequestInterface $request): RouteInterface
    {
        $route = $request->getAttribute(Router::ROUTE_ATTRIBUTE, null);
        if ($route === null) {
            throw new ScopeException('Unable to resolve Route, invalid request scope');
        }

        return $route;
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initRegistry(ContainerInterface $container): LoaderRegistryInterface
    {
        return new LoaderRegistry([
            $container->get(PhpFileLoader::class)
        ]);
    }
}
