<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

final class TestAuthHttpProvider implements ActorProviderInterface
{
    public function getActor(TokenInterface $token): ?object
    {
        if ($token->getID() === 'ok') {
            return new \stdClass();
        }

        return null;
    }
}
