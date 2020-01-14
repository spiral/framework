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
use Spiral\App\User\UserRepository;
use Spiral\App\ViewEngine\TestEngine;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Bootloader\Security\ValidationBootloader;
use Spiral\Bootloader\Views\ViewsBootloader;
use Spiral\Core\CoreInterface;
use Spiral\Domain\CycleInterceptor;
use Spiral\Domain\FilterInterceptor;
use Spiral\Domain\GuardInterceptor;
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
        PermissionsInterface $rbac,
        ViewsBootloader $views,
        ValidationBootloader $validation,
        HttpBootloader $http
    ): void {
        $authBootloader->addActorProvider(UserRepository::class);

        $rbac->addRole('user');
        $rbac->associate('user', '*');

        $rbac->addRole('demo');
        $rbac->associate('demo', 'demo.*');

        $views->addDirectory('custom', __DIR__ . '/../../views/custom/');
        $views->addEngine(TestEngine::class);

        $validation->addAlias('aliased', 'notEmpty');
        $validation->addChecker('my', MyChecker::class);
        $validation->addCondition('cond', MyCondition::class);

        $http->addJsonContentType('application/vnd.api+json');
    }
}
