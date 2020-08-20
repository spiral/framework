<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Auth\Stub;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\TokenInterface;

class TestAuthHttpProvider implements ActorProviderInterface
{
    public function getActor(TokenInterface $token): ?object
    {
        if ($token->getID() === 'ok') {
            return new \stdClass();
        }

        return null;
    }
}
