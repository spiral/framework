<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Security;

use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\ScopeException;
use Spiral\Security\Exception\GuardException;

/**
 * Resolves Actor dynamically, using current active IoC scope.
 */
final class ScopeGuard implements GuardInterface
{
    /** @var ActorInterface|null */
    private $actor = null;

    /** @var ContainerInterface */
    private $container;

    /** @var PermissionsInterface */
    private $permissions = null;

    /**@var array */
    private $roles = [];

    /**
     * @param PermissionsInterface $permissions
     * @param ContainerInterface   $actorScope
     * @param array                $roles Session specific roles.
     */
    public function __construct(
        PermissionsInterface $permissions,
        ContainerInterface $actorScope,
        array $roles = []
    ) {
        $this->roles = $roles;
        $this->container = $actorScope;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function allows(string $permission, array $context = []): bool
    {
        $allows = false;
        foreach ($this->getRoles() as $role) {
            if (!$this->permissions->hasRole($role)) {
                continue;
            }

            $rule = $this->permissions->getRule($role, $permission);

            //Checking our rule
            $allows = $allows || $rule->allows($this->getActor(), $permission, $context);
        }

        return $allows;
    }

    /**
     * Currently active actor/session roles.
     *
     * @return array
     *
     * @throws GuardException
     */
    public function getRoles(): array
    {
        return array_merge($this->roles, $this->getActor()->getRoles());
    }

    /**
     * Create instance of guard with session specific roles (existed roles will be droppped).
     *
     * @param array $roles
     * @return ScopeGuard
     */
    public function withRoles(array $roles): ScopeGuard
    {
        $guard = clone $this;
        $guard->roles = $roles;

        return $guard;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ScopeException
     */
    public function getActor(): ActorInterface
    {
        if (!is_null($this->actor)) {
            return $this->actor;
        }

        try {
            return $this->container->get(ActorInterface::class);
        } catch (ContainerException $e) {
            throw new ScopeException('Unable to resolve Actor from the scope', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withActor(ActorInterface $actor): GuardInterface
    {
        $guard = clone $this;
        $guard->actor = $actor;

        return $guard;
    }
}
