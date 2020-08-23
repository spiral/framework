<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Security\Actors;

use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\Actor\NullActor;

class NullActorTest extends TestCase
{
    public function testGetRoles(): void
    {
        /** @var ActorInterface $actor */
        $actor = new NullActor();

        $this->assertEquals([], $actor->getRoles());
    }
}
