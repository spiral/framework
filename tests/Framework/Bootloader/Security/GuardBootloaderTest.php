<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

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
use Spiral\Tests\Framework\BaseTest;

final class GuardBootloaderTest extends BaseTest
{
    public function testPermissionsInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(PermissionsInterface::class, PermissionManager::class);
    }

    public function testRulesInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(RulesInterface::class, RuleManager::class);
    }

    public function testGuardInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(GuardInterface::class, GuardScope::class);
    }

    public function testPermissionsProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(PermissionsProviderInterface::class, GuardPermissionsProvider::class);
    }

    public function testActorInterfaceBinding(): void
    {
        $this->assertContainerBound(ActorInterface::class, Guest::class);
    }
}
