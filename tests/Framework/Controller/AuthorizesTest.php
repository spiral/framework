<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Controller;

use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\ScopeException;
use Spiral\Security\Actor\Actor;
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;
use Spiral\Security\GuardInterface;
use Spiral\Security\GuardScope;
use Spiral\App\Controller\AuthController;
use Spiral\Tests\Framework\BaseTestCase;

final class AuthorizesTest extends BaseTestCase
{
    public function testAuthException(): void
    {
        $this->expectException(ControllerException::class);
        $this->expectExceptionMessage("Unauthorized permission 'do'");

        $this->getContainer()
            ->bind(ActorInterface::class, new Guest());

        $this->getContainer()
            ->get(CoreInterface::class)
            ->callAction(AuthController::class, 'do');
    }

    public function testAuth(): void
    {
        $this->getContainer()->bind(ActorInterface::class, new Actor(['user']));

        $r = $this->getContainer()
            ->get(CoreInterface::class)
            ->callAction(AuthController::class, 'do');

        self::assertSame('ok', $r);
    }

    public function testAuthNoActor(): void
    {
        $this->expectException(ScopeException::class);

        $this->getContainer()->removeBinding(ActorInterface::class);

        $this->getContainer()->get(CoreInterface::class)->callAction(AuthController::class, 'do');
    }

    public function testWithRoles(): void
    {
        $g = $this->getContainer()->get(GuardInterface::class);
        self::assertInstanceOf(GuardScope::class, $g);

        self::assertSame(['guest'], $g->getRoles());

        $g2 = $g->withRoles(['user']);

        self::assertSame(['guest'], $g->getRoles());
        self::assertSame(['user', 'guest'], $g2->getRoles());
    }

    public function testWithActor(): void
    {
        $g = $this->getContainer()->get(GuardInterface::class);
        self::assertInstanceOf(GuardScope::class, $g);

        self::assertSame(['guest'], $g->getRoles());

        $g2 = $g->withActor(new Actor(['admin']));

        self::assertSame(['admin'], $g2->getRoles());
        self::assertSame(['guest'], $g->getRoles());
    }
}
