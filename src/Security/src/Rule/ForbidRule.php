<?php

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Core\Attribute\Singleton;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;

/**
 * Always negative rule.
 */
#[Singleton]
final class ForbidRule implements RuleInterface
{
    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        return false;
    }
}
