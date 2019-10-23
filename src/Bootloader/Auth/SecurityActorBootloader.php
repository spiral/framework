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
use Spiral\Auth\Bootloader\AuthBootloader;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Security\GuardBootloader;
use Spiral\Security\Actor\Guest;
use Spiral\Security\ActorInterface;

final class SecurityActorBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AuthBootloader::class,
        GuardBootloader::class
    ];

    protected const BINDINGS = [
        ActorInterface::class => [self::class, 'actor']
    ];

    private function actor(AuthContextInterface $context): ActorInterface
    {
        // todo: get the actor from the context

        return new Guest();
    }
}