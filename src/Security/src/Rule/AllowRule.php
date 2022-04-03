<?php

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;

/**
 * Always positive rule.
 */
final class AllowRule implements RuleInterface, SingletonInterface
{
    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        return true;
    }
}
