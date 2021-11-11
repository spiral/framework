<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security\Rule;

use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;

/**
 * Wraps callable expression.
 */
final class CallableRule implements RuleInterface
{
    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        return (bool)call_user_func($this->callable, $actor, $permission, $context);
    }
}
