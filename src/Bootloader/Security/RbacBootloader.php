<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Security\Guard;
use Spiral\Security\GuardInterface;
use Spiral\Security\PermissionManager;
use Spiral\Security\PermissionsInterface;
use Spiral\Security\RuleManager;
use Spiral\Security\RulesInterface;

final class RbacBootloader extends Bootloader implements SingletonInterface
{
    const SINGLETONS = [
        PermissionsInterface::class => PermissionManager::class,
        RulesInterface::class       => RuleManager::class
    ];

    const BINDINGS = [
        GuardInterface::class => Guard::class
    ];
}