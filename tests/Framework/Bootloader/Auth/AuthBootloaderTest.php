<?php

declare(strict_types=1);

namespace Framework\Bootloader\Auth;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthScope;
use Spiral\Tests\Framework\BaseTest;

final class AuthBootloaderTest extends BaseTest
{
    public function testAuthScopeBinding(): void
    {
        $this->assertContainerBoundAsSingleton(AuthScope::class, AuthScope::class);
    }

    public function testActorProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ActorProviderInterface::class, ActorProviderInterface::class);
    }
}
