<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Security\ActorInterface;
use Spiral\Security\Actors\Actor;
use Spiral\Security\Rules\AllowRule;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Controllers\AuthorizesController;

class AuthorizesTraitTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Security\Exceptions\GuardException
     * @expectedExceptionMessage Unable to get Guard Actor, no value set
     */
    public function testPermissionCheckNoActor()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->app->callAction(AuthorizesController::class, 'allows');
    }

    /**
     * @expectedException \Spiral\Security\Exceptions\GuardException
     * @expectedExceptionMessage Unable to get Guard Actor, no value set
     */
    public function testAuthorizeCheckNoActor()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->app->callAction(AuthorizesController::class, 'authorizes');
    }

    public function testPermissionCheckWithActorViaContext()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->assertTrue(
            $this->app->callAction(AuthorizesController::class, 'allows', [], [
                ActorInterface::class => new Actor(['user'])
            ])
        );
    }

    public function testPermissionAuthorizesWithActorViaContext()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->assertTrue(
            $this->app->callAction(AuthorizesController::class, 'authorizes', [], [
                ActorInterface::class => new Actor(['user'])
            ])
        );
    }

    public function testPermissionCheckWithActorViaCotainer()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->container->bind(ActorInterface::class, new Actor(['user']));

        $this->assertTrue(
            $this->app->callAction(AuthorizesController::class, 'allows')
        );
    }

    public function testPermissionAuthorizesWithActorViaCotainer()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->container->bind(ActorInterface::class, new Actor(['user']));

        $this->assertTrue(
            $this->app->callAction(AuthorizesController::class, 'authorizes')
        );
    }

    public function testPermissionCheckWithActorViaContextWrongRole()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->assertFalse(
            $this->app->callAction(AuthorizesController::class, 'allows', [], [
                ActorInterface::class => new Actor(['guest'])
            ])
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage Unauthorized permission 'do'
     */
    public function testPermissionAuthorizesWithActorViaContextWrongRole()
    {
        $this->permissions->addRole('user');
        $this->permissions->associate('user', 'do', AllowRule::class);

        $this->assertFalse(
            $this->app->callAction(AuthorizesController::class, 'authorizes', [], [
                ActorInterface::class => new Actor(['guest'])
            ])
        );
    }
}