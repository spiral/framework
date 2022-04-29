<?php

declare(strict_types=1);

namespace Spiral\Security\Actor;

use Spiral\Security\ActorInterface;

/**
 * Actor without any roles.
 */
final class NullActor implements ActorInterface
{
    public function getRoles(): array
    {
        return [];
    }
}
