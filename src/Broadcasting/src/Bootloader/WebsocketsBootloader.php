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
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;

#[Singleton]
final class WebsocketsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        BroadcastingBootloader::class,
    ];

    public function boot(BinderInterface $binder): void
    {
        $binder->bindSingleton(AuthorizationMiddleware::class, static fn (
            BroadcastInterface $broadcast,
            ResponseFactoryInterface $responseFactory,
            BroadcastConfig $config,
            ?EventDispatcherInterface $dispatcher = null,
        ): AuthorizationMiddleware => new AuthorizationMiddleware(
            $broadcast,
            $responseFactory,
            $config->getAuthorizationPath(),
            $dispatcher
        ));
    }
}
