<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Security\Exception\PermissionException;
use Spiral\Security\Exception\RoleException;
use Spiral\Security\Rule\AllowRule;
use Spiral\Security\Rule\ForbidRule;

/**
 * Default implementation of associations repository and manager. Provides ability to set
 * permissions in bulk using * syntax.
 *
 * Attention, this class is serializable and can be cached in memory.
 *
 * Example:
 * $associations->associate('admin', '*');
 * $associations->associate('editor', 'posts.*', Allows::class);
 * $associations->associate('user', 'posts.*', Forbid::class);
 */
final class PermissionManager implements PermissionsInterface, SingletonInterface
{
    /**
     * Roles associated with their permissions.
     *
     * @var array
     */
    private $permissions = [];

    /** @var Matcher */
    private $matcher;

    /**@var RulesInterface */
    private $rules;

    /** @var string */
    private $defaultRule = ForbidRule::class;

    public function __construct(RulesInterface $rules, string $defaultRule = ForbidRule::class)
    {
        $this->matcher = new Matcher();
        $this->rules = $rules;
        $this->defaultRule = $defaultRule;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        return array_key_exists($role, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(string $role): PermissionManager
    {
        if ($this->hasRole($role)) {
            throw new RoleException("Role '{$role}' already exists");
        }

        $this->permissions[$role] = [
            //No associated permissions
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole(string $role): PermissionManager
    {
        if (!$this->hasRole($role)) {
            throw new RoleException("Undefined role '{$role}'");
        }

        unset($this->permissions[$role]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return array_keys($this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(string $role): array
    {
        if (!$this->hasRole($role)) {
            throw new RoleException("Undefined role '{$role}'");
        }

        return $this->permissions[$role];
    }

    /**
     * {@inheritdoc}
     */
    public function getRule(string $role, string $permission): RuleInterface
    {
        if (!$this->hasRole($role)) {
            throw new RoleException("Undefined role '{$role}'");
        }

        //Behaviour points to rule
        return $this->rules->get($this->findRule($role, $permission));
    }

    /**
     * {@inheritdoc}
     */
    public function associate(string $role, string $permission, string $rule = AllowRule::class): PermissionManager
    {
        if (!$this->hasRole($role)) {
            throw new RoleException("Undefined role '{$role}'");
        }

        if (!$this->rules->has($rule)) {
            throw new PermissionException("Undefined rule '{$rule}'");
        }

        $this->permissions[$role][$permission] = $rule;

        return $this;
    }

    /**
     * Associate role/permission with Forbid rule.
     *
     *
     * @throws RoleException
     * @throws PermissionException
     */
    public function deassociate(string $role, string $permission): PermissionManager
    {
        return $this->associate($role, $permission, ForbidRule::class);
    }

    /**
     *
     * @throws PermissionException
     */
    private function findRule(string $role, string $permission): string
    {
        if (isset($this->permissions[$role][$permission])) {
            //O(1) check
            return $this->permissions[$role][$permission];
        }

        //Matching using star syntax
        foreach ($this->permissions[$role] as $pattern => $rule) {
            if ($this->matcher->matches($permission, $pattern)) {
                return $rule;
            }
        }

        return $this->defaultRule;
    }
}
