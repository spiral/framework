<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Bootloader;

use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;

final class WebsocketsBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        BroadcastingBootloader::class,
    ];

    public function start(Container $container, HttpBootloader $http, BroadcastConfig $config): void
    {
        $container->bindSingleton(AuthorizationMiddleware::class, static fn(BroadcastInterface $broadcast, ResponseFactoryInterface $responseFactory, BroadcastConfig $config): AuthorizationMiddleware => new AuthorizationMiddleware(
            $broadcast,
            $responseFactory,
            $config->getAuthorizationPath()
        ));


        if ($config->getAuthorizationPath() !== null) {
            $http->addMiddleware(AuthorizationMiddleware::class);
        }
    }
}
