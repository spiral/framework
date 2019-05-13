<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Bootloader;

use Spiral\App\Checker\MyChecker;
use Spiral\App\Condition\MyCondition;
use Spiral\App\Controller\TestController;
use Spiral\App\ViewEngine\TestEngine;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Security\ValidationBootloader;
use Spiral\Bootloader\Views\ViewsBootloader;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Controller;
use Spiral\Security\PermissionsInterface;

class AppBootloader extends Bootloader
{
    const BOOT = true;

    public function boot(
        RouterInterface $router,
        PermissionsInterface $rbac,
        ViewsBootloader $bootloader,
        ValidationBootloader $validation
    ) {
        $rbac->addRole('user');
        $rbac->associate('user', '*');

        $route = new Route(
            '/<action>[/<name>]',
            new Controller(TestController::class)
        );

        $router->setDefault($route->withDefaults(['name' => 'Dave']));

        $bootloader->addDirectory('custom', __DIR__ . '/../../views/custom/');
        $bootloader->addEngine(TestEngine::class);

        $validation->addAlias('aliased', 'notEmpty');
        $validation->addChecker('my', MyChecker::class);
        $validation->addCondition('cond', MyCondition::class);
    }
}