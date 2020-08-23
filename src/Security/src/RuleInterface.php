<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Security\Exception\RuleException;

/**
 * Context specific operation rule.
 */
interface RuleInterface
{
    /**
     * @param ActorInterface $actor
     * @param string         $permission
     * @param array          $context
     * @return bool
     *
     * @throws RuleException
     */
    public function allows(ActorInterface $actor, string $permission, array $context): bool;
}
