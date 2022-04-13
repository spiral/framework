<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Driver\AuthorizationMiddleware;
use Spiral\Core\Container\SingletonInterface;

final class WebsocketsBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        BroadcastingBootloader::class,
    ];

    public function start(HttpBootloader $http, BroadcastConfig $config): void
    {
        if ($config->getAuthorizationPath() !== null) {
            $http->addMiddleware(AuthorizationMiddleware::class);
        }
    }
}
