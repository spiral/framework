<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Bootloader;

use Spiral\App\Controller\TestController;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Controller;

class AppBootloader extends Bootloader
{
    const BOOT = true;

    public function boot(RouterInterface $router)
    {
        $route = new Route('/<action>[/<name>]', new Controller(TestController::class));

        $router->setDefault($route->withDefaults(['name' => 'Dave']));
    }
}