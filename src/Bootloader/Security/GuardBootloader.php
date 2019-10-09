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
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;
use Spiral\Security\GuardInterface;
use Spiral\Security\PermissionManager;
use Spiral\Security\PermissionsInterface;
use Spiral\Security\RuleManager;
use Spiral\Security\RulesInterface;
use Spiral\Security\ScopeGuard;

final class GuardBootloader extends Bootloader
{
    protected const SINGLETONS = [
        PermissionsInterface::class => PermissionManager::class,
        RulesInterface::class       => RuleManager::class,
        GuardInterface::class       => ScopeGuard::class
    ];

    protected const BINDINGS = [
        ActorInterface::class => Guest::class
    ];
}
