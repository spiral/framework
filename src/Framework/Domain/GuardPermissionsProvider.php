<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Doctrine\Common\Annotations\AnnotationReader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;

class GuardPermissionsProvider implements PermissionsProviderInterface, SingletonInterface
{
    /** @var array */
    private $cache = [];

    /** @var string|null */
    private $namespace;

    /** @var AnnotationReader */
    private $reader;

    public function __construct(AnnotationReader $reader, string $namespace = null)
    {
        $this->reader = $reader;
        $this->namespace = $namespace;
    }

    /**
     * Get method RBAC permission if any. Automatically merges with controller namespace.
     *
     * @param string $controller
     * @param string $action
     * @return array|null
     *
     */
    public function getPermissions(string $controller, string $action): ?array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->generatePermission($controller, $action);
        }

        return $this->cache[$key];
    }

    private function generatePermission(string $controller, string $action): ?array
    {
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return null;
        }

        $guarded = $this->reader->getMethodAnnotation($method, Guarded::class);
        if (!$guarded instanceof Guarded) {
            return null;
        }

        return $this->makePermission(
            $guarded,
            $method,
            $this->reader->getClassAnnotation($method->getDeclaringClass(), GuardNamespace::class)
        );
    }

    /**
     * Generates permissions for the method or controller.
     *
     * @param Guarded             $guarded
     * @param \ReflectionMethod   $method
     * @param GuardNamespace|null $ns
     * @return array
     */
    public function makePermission(Guarded $guarded, \ReflectionMethod $method, ?GuardNamespace $ns): array
    {
        $permission = [
            $guarded->permission ?? $method->getName(),
            ControllerException::FORBIDDEN,
            $guarded->errorMessage ?? sprintf('Unauthorized access `%s`', $guarded->permission ?? $method->getName()),
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
