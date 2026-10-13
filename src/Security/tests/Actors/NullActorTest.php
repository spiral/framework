<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Actors;

use PHPUnit\Framework\TestCase;
use Spiral\Security\Actor\NullActor;
use Spiral\Security\ActorInterface;

final class NullActorTest extends TestCase
{
    public function testGetRoles(): void
    {
        /** @var ActorInterface $actor */
        $actor = new NullActor();

        self::assertEquals([], $actor->getRoles());
    }
}
