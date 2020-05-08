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

/**
 * Interceptor provides the ability to check the access to the controllers and controller methods using security
 * component and annotations "Guarded" and "GuardNamespace".
 */
final class GuardInterceptor implements CoreInterceptorInterface
{
    /** @var GuardInterface */
    private $guard;

    /** @var string|null */
    private $namespace;

    /** @var array */
    private $cache = [];

    /**
     * @param GuardInterface $guard
     * @param string|null    $namespace
     */
    public function __construct(GuardInterface $guard, string $namespace = null)
    {
        $this->guard = $guard;
        $this->namespace = $namespace;
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
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = null;
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return null;
        }

        $reader = new AnnotationReader();

        /** @var GuardNamespace $namespace */
        $namespace = $reader->getClassAnnotation(
            $method->getDeclaringClass(),
            GuardNamespace::class
        );

        /** @var Guarded $action */
        $action = $reader->getMethodAnnotation(
            $method,
            Guarded::class
        );

        if ($action === null) {
            return null;
        }

        return $this->cache[$key] = $this->makePermission($action, $method, $namespace);
    }

    /**
     * Generates permissions for the method or controller.
     *
     * @param Guarded             $guarded
     * @param \ReflectionMethod   $method
     * @param GuardNamespace|null $ns
     * @return array
     */
    private function makePermission(Guarded $guarded, \ReflectionMethod $method, ?GuardNamespace $ns): array
    {
        $permission = [
            $guarded->permission ?? $method->getName(),
            ControllerException::FORBIDDEN
        ];

        if ($guarded->permission === null && $ns === null) {
            throw new InterceptorException(
                sprintf(
                    'Unable to apply @Guarded without name or @GuardNamespace on `%s`->`%s`',
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                )
            );
        }

        if ($ns !== null) {
            $permission[0] = sprintf('%s.%s', $ns->namespace, $permission[0]);
        }

        if ($this->namespace !== null) {
            // global namespace
            $permission[0] = sprintf('%s.%s', $this->namespace, $permission[0]);
        }

        switch ($guarded->else) {
            case 'unauthorized':
                $permission[1] = ControllerException::UNAUTHORIZED;
                break;
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

        return $permission;
    }
}
