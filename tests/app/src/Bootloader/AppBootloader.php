<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\App\ViewEngine\TestEngine;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Bootloader\Http\JsonPayloadsBootloader;
use Spiral\Core\CoreInterface;
use Spiral\Domain\GuardInterceptor;
use Spiral\Security\PermissionsInterface;
use Spiral\Views\Bootloader\ViewsBootloader;

class AppBootloader extends DomainBootloader
{
    protected const SINGLETONS = [
        CoreInterface::class => [self::class, 'domainCore']
    ];

    protected const INTERCEPTORS = [
        GuardInterceptor::class,
    ];

    public function init(PermissionsInterface $rbac, ViewsBootloader $views, JsonPayloadsBootloader $json): void
    {
        $rbac->addRole('user');
        $rbac->associate('user', '*');

        $rbac->addRole('demo');
        $rbac->associate('demo', 'demo.*');

        $views->addDirectory('custom', __DIR__ . '/../../views/custom/');
        $views->addDirectory('stempler', __DIR__ . '/../../views/stempler/');
        $views->addEngine(TestEngine::class);

        $json->addContentType('application/vnd.api+json');
    }
}
