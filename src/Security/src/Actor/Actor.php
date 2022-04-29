<?php

declare(strict_types=1);

namespace Spiral\Security\Actor;

use Spiral\Security\ActorInterface;

/**
 * Simple actor with role dependency.
 */
class Actor implements ActorInterface
{
    public function __construct(
        private readonly array $roles
    ) {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
