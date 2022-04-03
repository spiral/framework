<?php

declare(strict_types=1);

namespace Spiral\Security\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Security\GuardInterface;

/**
 * Embeds GuardInterface functionality into class and provides ability to isolate permissions
 * using guard namespace. GuardedTrait operates using global container scope.
 */
trait GuardedTrait
{
    /**
     * @throws ScopeException
     */
    public function getGuard(): GuardInterface
    {
        $container = ContainerScope::getContainer();
        if (empty($container) || !$container->has(GuardInterface::class)) {
            throw new ScopeException(
                'Unable to get `GuardInterface`, binding is missing or container scope is not set'
            );
        }

        return $container->get(GuardInterface::class);
    }

    protected function allows(string $permission, array $context = []): bool
    {
        return $this->getGuard()->allows($this->resolvePermission($permission), $context);
    }

    protected function denies(string $permission, array $context = []): bool
    {
        return !$this->allows($permission, $context);
    }

    /**
     * Automatically prepend permission name with local RBAC namespace.
     */
    protected function resolvePermission(string $permission): string
    {
        if (\defined('static::GUARD_NAMESPACE')) {
            // Yay! Isolation
            $permission = \constant(static::class . '::' . 'GUARD_NAMESPACE') . '.' . $permission;
        }

        return $permission;
    }
}
