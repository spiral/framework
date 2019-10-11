<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Controller;

use Spiral\App\Controller\AuthController;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Framework\BaseTest;
use Spiral\Security\Actor\Actor;
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;
use Spiral\Security\GuardInterface;
use Spiral\Security\GuardScope;

class AuthorizesTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Core\Exception\ControllerException
     * @expectedExceptionMessage Unauthorized permission 'do'
     */
    public function testAuthException(): void
    {
        $app = $this->makeApp();
        $app->get(Container::class)->bind(ActorInterface::class, new Guest());

        $r = $app->get(CoreInterface::class)->callAction(AuthController::class, 'do');
    }

    public function testAuth(): void
    {
        $app = $this->makeApp();
        $app->get(Container::class)->bind(ActorInterface::class, new Actor(['user']));

        $r = $app->get(CoreInterface::class)->callAction(AuthController::class, 'do');
        $this->assertSame('ok', $r);
    }

    /**
     * @expectedException \Spiral\Core\Exception\ScopeException
     */
    public function testAuthNoActor(): void
    {
        $app = $this->makeApp();
        $app->getContainer()->removeBinding(ActorInterface::class);

        $app->get(CoreInterface::class)->callAction(AuthController::class, 'do');
    }

    public function testWithRoles(): void
    {
        $app = $this->makeApp();
        $g = $app->get(GuardInterface::class);
        $this->assertInstanceOf(GuardScope::class, $g);

        $this->assertSame(['guest'], $g->getRoles());

        $g2 = $g->withRoles(['user']);

        $this->assertSame(['guest'], $g->getRoles());
        $this->assertSame(['user', 'guest'], $g2->getRoles());
    }

    public function testWithActor(): void
    {
        $app = $this->makeApp();
        $g = $app->get(GuardInterface::class);
        $this->assertInstanceOf(GuardScope::class, $g);

        $this->assertSame(['guest'], $g->getRoles());

        $g2 = $g->withActor(new Actor(['admin']));

        $this->assertSame(['admin'], $g2->getRoles());
        $this->assertSame(['guest'], $g->getRoles());
    }
}
