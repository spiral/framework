<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Security\GuardInterface;

/**
 * Interceptor provides the ability to check the access to the controllers and controller methods using security
 * component and annotations "Guarded" and "GuardNamespace".
 */
final class GuardInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly GuardInterface $guard,
        private readonly PermissionsProviderInterface $permissions
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $permission = $this->permissions->getPermission($controller, $action);

        if ($permission->ok && !$this->guard->allows($permission->permission, $parameters)) {
            throw new ControllerException($permission->message, $permission->code);
        }

        return $core->callAction($controller, $action, $parameters);
    }
}
