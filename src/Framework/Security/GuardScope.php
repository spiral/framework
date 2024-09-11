<?php

declare(strict_types=1);

namespace Spiral\Security;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Exception\ScopeException;
use Spiral\Security\Exception\GuardException;

/**
 * Resolves Actor dynamically, using current active IoC scope.
 * @deprecated Use  {@see GuardInterface} instead. Will be removed in v4.0.
 */
final class GuardScope implements GuardInterface
{
    private ?ActorInterface $actor = null;

    public function __construct(
        private readonly PermissionsInterface $permissions,
        #[Proxy] private readonly ContainerInterface $container,
        private array $roles = []
    ) {
    }

    /**
     * @throws ScopeException
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
     * @throws GuardException
     * @throws ScopeException
     */
    public function getRoles(): array
    {
        return \array_merge($this->roles, $this->getActor()->getRoles());
    }

    /**
     * Create instance of guard with session specific roles (existed roles will be droppped).
     */
    public function withRoles(array $roles): GuardScope
    {
        $guard = clone $this;
        $guard->roles = $roles;

        return $guard;
    }

    /**
     * @throws ScopeException
     */
    public function getActor(): ActorInterface
    {
        if (!\is_null($this->actor)) {
            return $this->actor;
        }

        try {
            return $this->container->get(ActorInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to resolve Actor from the scope', $e->getCode(), $e);
        }
    }

    public function withActor(ActorInterface $actor): GuardInterface
    {
        $guard = clone $this;
        $guard->actor = $actor;

        return $guard;
    }
}
