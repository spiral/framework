<?php

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Security\Exception\RuleException;

/**
 * Provides ability to represent security rules using string names.
 *
 * Example:
 * $rules->set('author-rule', Rules\AuthorRule::class);
 *
 * //Allow user to edit post based on "author-rule"
 * $permissions->associate('user', 'post.edit', 'author-rule');
 */
interface RulesInterface
{
    /**
     * Register new rule class under given name. Rule must either implement RuleInterface or
     * support signature: (ActorInterface $actor, $operation, array $context)
     *
     * Technically you can use this method as you use container bindings.
     *
     * @param string $name Rule name in a string form.
     * @param string|array|callable|RuleInterface|null $rule Rule, if kept as null rule name must be treated as class
     * name for RuleInterface.
     * @throws RuleException
     */
    public function set(string $name, mixed $rule = null): self;

    /**
     * Remove created rule.
     *
     * @throws RuleException
     */
    public function remove(string $name): self;

    /**
     * Check if requested rule exists.
     */
    public function has(string $name): bool;

    /**
     * Get rule object based on it's name.
     *
     * @throws RuleException
     */
    public function get(string $name): RuleInterface;
}
