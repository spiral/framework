<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Domain\GuardPermissionsProvider;
use Spiral\Domain\PermissionsProviderInterface;
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;
use Spiral\Security\GuardInterface;
use Spiral\Security\GuardScope;
use Spiral\Security\PermissionManager;
use Spiral\Security\PermissionsInterface;
use Spiral\Security\RuleManager;
use Spiral\Security\RulesInterface;

final class GuardBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class,
    ];

    protected const SINGLETONS = [
        PermissionsInterface::class => PermissionManager::class,
        RulesInterface::class => RuleManager::class,
        GuardInterface::class => GuardScope::class,
        PermissionsProviderInterface::class => GuardPermissionsProvider::class,
    ];

    protected const BINDINGS = [
        ActorInterface::class => Guest::class,
    ];
}
