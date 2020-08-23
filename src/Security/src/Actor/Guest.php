<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security\Actor;

use Spiral\Security\ActorInterface;

/**
 * Actor with defined actor.
 */
final class Guest implements ActorInterface
{
    public const ROLE = 'guest';

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return [static::ROLE];
    }
}
