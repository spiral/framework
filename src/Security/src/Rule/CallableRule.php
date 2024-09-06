<?php

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;

/**
 * Wraps callable expression.
 */
final class CallableRule implements RuleInterface
{
    private readonly \Closure $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable(...);
    }

    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        return (bool) ($this->callable)($actor, $permission, $context);
    }
}
