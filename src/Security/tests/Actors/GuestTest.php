<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Actors;

use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\Actor\Guest;

class GuestTest extends TestCase
{
    public function testGetRoles(): void
    {
        /** @var ActorInterface $actor */
        $actor = new Guest();

        self::assertEquals([Guest::ROLE], $actor->getRoles());
    }
}
