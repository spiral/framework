<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var GuardInterface */
    private $guard;

    /** @var PermissionsProviderInterface */
    private $permissions;

    public function __construct(GuardInterface $guard, PermissionsProviderInterface $permissions)
    {
        $this->guard = $guard;
        $this->permissions = $permissions;
    }

    /**
     * @inheritDoc
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        $permission = $this->permissions->getPermission($controller, $action);

        if ($permission->ok && !$this->guard->allows($permission->permission, $parameters)) {
            throw new ControllerException($permission->message, $permission->code);
        }

        return $core->callAction($controller, $action, $parameters);
    }
}
