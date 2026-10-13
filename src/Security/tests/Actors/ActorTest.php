<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Actors;

use PHPUnit\Framework\TestCase;
use Spiral\Security\Actor\Actor;
use Spiral\Security\ActorInterface;

final class ActorTest extends TestCase
{
    public function testGetRoles(): void
    {
        $roles = ['user', 'admin'];

        /** @var ActorInterface $actor */
        $actor = new Actor($roles);

        self::assertEquals($roles, $actor->getRoles());
    }
}
