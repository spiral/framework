<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Bootloader;

use Psr\EventDispatcher\EventDispatcherInterface;
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

    public function boot(Container $container): void
    {
        $container->bindSingleton(AuthorizationMiddleware::class, static function (
            BroadcastInterface $broadcast,
            ResponseFactoryInterface $responseFactory,
            BroadcastConfig $config,
            ?EventDispatcherInterface $dispatcher = null
        ): AuthorizationMiddleware {
            return new AuthorizationMiddleware(
                $broadcast,
                $responseFactory,
                $config->getAuthorizationPath(),
                $dispatcher
            );
        });
    }
}
