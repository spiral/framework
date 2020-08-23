<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Security\Exception\GuardException;

/**
 * Guard interface is responsible for high level permission management.
 */
interface GuardInterface
{
    /**
     * Check if given operation are allowed. Has to check associations between operation and
     * actor/session roles based on given rules (binary vs context specific).
     *
     * @param string $permission
     * @param array  $context Permissions specific context.
     * @return bool
     *
     * @throws GuardException
     */
    public function allows(string $permission, array $context = []): bool;

    /**
     * Get associated actor instance.
     *
     * @return ActorInterface
     *
     * @throws GuardException
     */
    public function getActor(): ActorInterface;

    /**
     * Create an instance or GuardInterface associated with different actor. Method must not
     * alter existed guard which has to be counted as immutable.
     *
     * @param ActorInterface $actor
     * @return self
     */
    public function withActor(ActorInterface $actor): GuardInterface;
}
