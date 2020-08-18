<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

class TestProvider implements ActorProviderInterface
{
    public function getActor(TokenInterface $token): ?object
    {
        if ($token->getPayload()['ok']) {
            return new \stdClass();
        }

        return null;
    }
}
