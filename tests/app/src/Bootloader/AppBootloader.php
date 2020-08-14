<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\App\Checker\MyChecker;
use Spiral\App\Condition\MyCondition;
use Spiral\App\Controller\AuthController;
use Spiral\App\Controller\TestController;
use Spiral\App\User\UserRepository;
use Spiral\App\ViewEngine\TestEngine;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Bootloader\Http\JsonPayloadsBootloader;
use Spiral\Bootloader\Security\ValidationBootloader;
use Spiral\Bootloader\Views\ViewsBootloader;
use Spiral\Core\CoreInterface;
use Spiral\Domain\CycleInterceptor;
use Spiral\Domain\FilterInterceptor;
use Spiral\Domain\GuardInterceptor;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Controller;
use Spiral\Security\PermissionsInterface;

class AppBootloader extends DomainBootloader
{
    protected const SINGLETONS = [
        CoreInterface::class => [self::class, 'domainCore']
    ];

    protected const INTERCEPTORS = [
        CycleInterceptor::class,
        GuardInterceptor::class,
        FilterInterceptor::class
    ];

    public function boot(
        \Spiral\Bootloader\Auth\AuthBootloader $authBootloader,
        RouterInterface $router,
        PermissionsInterface $rbac,
        ViewsBootloader $views,
        ValidationBootloader $validation,
        JsonPayloadsBootloader $json
    ): void {
        $authBootloader->addActorProvider(UserRepository::class);

        $rbac->addRole('user');
        $rbac->associate('user', '*');

        $rbac->addRole('demo');
        $rbac->associate('demo', 'demo.*');

        $route = new Route(
            '/<action>[/<name>]',
            new Controller(TestController::class)
        );

        $router->setDefault($route->withDefaults(['name' => 'Dave']));

        $router->setRoute(
            'auth',
            new Route('/auth/<action>', new Controller(AuthController::class))
        );

        $views->addDirectory('custom', __DIR__ . '/../../views/custom/');
        $views->addEngine(TestEngine::class);

        $validation->addAlias('aliased', 'notEmpty');
        $validation->addChecker('my', MyChecker::class);
        $validation->addCondition('cond', MyCondition::class);

        $json->addContentType('application/vnd.api+json');
    }
}
