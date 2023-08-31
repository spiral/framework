<?php

declare(strict_types=1);

namespace Spiral\Security;

use Spiral\Security\Exception\PermissionException;
use Spiral\Security\Exception\RoleException;

/**
 * Class responsible for Role/Permission/Rule mapping.
 */
interface PermissionsInterface
{
    public function hasRole(string $role): bool;

    /**
     * Register new role.
     *
     * @throws RoleException
     */
    public function addRole(string $role): self;

    /**
     * Remove existed guard role and every association it has.
     *
     * @throws RoleException
     */
    public function removeRole(string $role): self;

    /**
     * List of every known role.
     */
    public function getRoles(): array;

    /**
     * Get list of all permissions and their rules associated with given role.
     *
     * @throws RoleException
     */
    public function getPermissions(string $role): array;

    /**
     * Get role/permission behaviour.
     *
     * @throws RoleException
     * @throws PermissionException
     */
    public function getRule(string $role, string $permission): RuleInterface;

    /**
     * Associate (allow) existed role with one or multiple permissions and specific behaviour.
     * Pattern based associations are supported using star syntax.
     *
     * $associations->allow('admin', '*', GuardInterface::ALLOW);
     * $associations->allow('user', 'posts.*', AuthorRule::class);
     *
     * Attention, role must be added previously!
     *
     * You can always create composite rules by creating decorating rule.
     *
     * @param string $rule Rule name previously registered in RulesInterface.
     *
     * @throws RoleException
     * @throws PermissionException
     *
     * @see addRole()
     * @see GuardInterface::ALLOW
     */
    public function associate(
        string $role,
        string $permission,
        string $rule = 'Spiral\Security\Rules\AllowRule'
    );
}
