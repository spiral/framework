<?php

declare(strict_types=1);

namespace Spiral\Auth;

/**
 * Manages association of token payload and actor.
 */
interface ActorProviderInterface
{
    /**
     * Return actor associated with token payload (if any). Must return null if actor not found.
     */
    public function getActor(TokenInterface $token): ?object;
}
