<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Domain;

use Doctrine\Common\Annotations\AnnotationReader;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;
use Spiral\Security\GuardInterface;

class GuardInterceptor implements CoreInterceptorInterface
{
    /** @var GuardInterface */
    private $guard;

    /** @var array */
    private $permissionCache = [];

    /**
     * @param GuardInterface $guard
     */
    public function __construct(GuardInterface $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        $permission = $this->getPermissions($controller, $action);

        if ($permission !== null && !$this->guard->allows($permission[0], $parameters)) {
            throw new ControllerException(
                sprintf(
                    'Unauthorized permission `%s` for action `%s`->`%s`',
                    $permission[0],
                    $controller,
                    $action
                ),
                $permission[1]
            );
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * Get method RBAC permission if any. Automatically merges with controller namespace.
     *
     * @param string $controller
     * @param string $action
     * @return array|null
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function getPermissions(string $controller, string $action): ?array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (array_key_exists($key, $this->permissionCache)) {
            return $this->permissionCache[$key];
        }

        $this->permissionCache[$key] = null;
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        $reader = new AnnotationReader();

        /** @var GuardNamespace $guardNamespace */
        $guardNamespace = $reader->getClassAnnotation($method->getDeclaringClass(), GuardNamespace::class);

        /** @var Guarded $guarded */
        $guarded = $reader->getMethodAnnotation($method, Guarded::class);

        if ($guarded === null) {
            return null;
        }

        if ($guarded->permission === null && $guardNamespace === null) {
            throw new InterceptorException(
                'Unable to apply @Guarded annotation without specified permission name or @GuardNamespace'
            );
        }

        $permission = [
            $guarded->permission ?? $action,
            ControllerException::FORBIDDEN
        ];

        if ($guardNamespace !== null) {
            $permission[0] = sprintf('%s.%s', $guardNamespace->namespace, $permission[0]);
        }

        switch ($guarded->else) {
            case 'badAction':
                $permission[1] = ControllerException::BAD_ACTION;
                break;
            case 'notFound':
                $permission[1] = ControllerException::NOT_FOUND;
                break;
            case 'error':
                $permission[1] = ControllerException::ERROR;
                break;
        }

        $this->permissionCache[$key] = $permission;

        return $permission;
    }
}
