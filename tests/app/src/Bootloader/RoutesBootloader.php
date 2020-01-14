<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\App\Controller\AuthController;
use Spiral\App\Controller\TestController;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Controller;

class RoutesBootloader extends Bootloader
{
    public function boot(RouterInterface $router)
    {
        $route = new Route(
            '/<action>[/<name>]',
            new Controller(TestController::class)
        );

        $router->setDefault($route->withDefaults(['name' => 'Dave']));

        $router->setRoute(
            'auth',
            new Route('/auth/<action>', new Controller(AuthController::class))
        );
    }
}
