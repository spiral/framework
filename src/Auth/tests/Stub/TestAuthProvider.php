<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

class TestAuthProvider implements ActorProviderInterface
{
    public function getActor(TokenInterface $token): ?object
    {
        if ($token->getPayload()['ok']) {
            return new \stdClass();
        }

        return null;
    }
}
