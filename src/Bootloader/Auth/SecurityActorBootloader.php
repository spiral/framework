<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Auth;

use Spiral\Auth\AuthContextInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Security\GuardBootloader;
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;

/**
 * Bridges the auth actor to RBAC Security actor.
 */
final class SecurityActorBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AuthBootloader::class,
        GuardBootloader::class
    ];

    protected const BINDINGS = [
        ActorInterface::class => [self::class, 'actor']
    ];

    /**
     * @param AuthContextInterface $context
     * @return ActorInterface
     */
    private function actor(AuthContextInterface $context): ActorInterface
    {
        $actor = $context->getActor();
        if ($actor instanceof ActorInterface) {
            return $actor;
        }

        return new Guest();
    }
}
