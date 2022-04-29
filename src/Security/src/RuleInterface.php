<?php

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Security\Exception\RuleException;

/**
 * Context specific operation rule.
 */
interface RuleInterface
{
    /**
     * @throws RuleException
     */
    public function allows(ActorInterface $actor, string $permission, array $context): bool;
}
