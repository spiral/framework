<?php

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;
use Spiral\Security\RulesInterface;

/**
 * Provides ability to evaluate multiple sub rules using boolean joiner.
 *
 * Example:
 *
 * class AuthorOrModeratorRule extends BooleanRule
 * {
 *      const BEHAVIOUR = self::AT_LEAST_ONE;
 *      const RULES     = [AuthorRule::class, ModeratorRule::class];
 * }
 */
abstract class CompositeRule implements RuleInterface
{
    protected const ALL          = 'ALL';
    protected const AT_LEAST_ONE = 'ONE';

    /** How to process results on sub rules. */
    protected const BEHAVIOUR = self::ALL;

    /** List of rules to be composited. */
    protected const RULES = [];

    public function __construct(
        private readonly RulesInterface $repository
    ) {
    }

    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        $allowed = 0;
        foreach (static::RULES as $rule) {
            $rule = $this->repository->get($rule);

            if ($rule->allows($actor, $permission, $context)) {
                if (static::BEHAVIOUR === self::AT_LEAST_ONE) {
                    return true;
                }

                $allowed++;
            } elseif (static::BEHAVIOUR === self::ALL) {
                return false;
            }
        }

        return $allowed === \count(static::RULES);
    }
}
