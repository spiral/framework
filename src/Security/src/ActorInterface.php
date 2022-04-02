<?php

declare(strict_types=1);

namespace Spiral\Security;

/**
 * ActorInterface used to represent active "player", in most of cases such interface is
 * implemented by User model.
 */
interface ActorInterface
{
    /**
     * Method must return list of roles associated with current actor is a form of array.
     */
    public function getRoles(): array;
}
