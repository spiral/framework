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

class AuthorizesTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Core\Exception\ControllerException
     * @expectedExceptionMessage Unauthorized permission 'do'
     */
    public function testAuthException()
    {
        $app = $this->makeApp();
        $app->get(Container::class)->bind(ActorInterface::class, new Guest());

        $r = $app->get(CoreInterface::class)->callAction(AuthController::class, 'do');
    }

    public function testAuth()
    {
        $app = $this->makeApp();
        $app->get(Container::class)->bind(ActorInterface::class, new Actor(['user']));

        $r = $app->get(CoreInterface::class)->callAction(AuthController::class, 'do');
        $this->assertSame('ok', $r);
    }
}