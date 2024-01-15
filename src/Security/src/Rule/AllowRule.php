<?php

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Core\Attribute\Singleton;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;

/**
 * Always positive rule.
 */
#[Singleton]
final class AllowRule implements RuleInterface
{
    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        return true;
    }
}
