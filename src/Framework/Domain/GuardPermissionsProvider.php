<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;

#[Singleton]
final class GuardPermissionsProvider implements PermissionsProviderInterface
{
    private const FAILURE_MAP = [
        'unauthorized' => ControllerException::UNAUTHORIZED,
        'badAction'    => ControllerException::BAD_ACTION,
        'notFound'     => ControllerException::NOT_FOUND,
        'error'        => ControllerException::ERROR,
    ];

    private array $cache = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly ?string $namespace = null
    ) {
    }

    /**
     * Get method RBAC permission if any. Automatically merges with controller namespace.
     */
    public function getPermission(string $controller, string $action): Permission
    {
        $key = \sprintf('%s:%s', $controller, $action);
        if (!\array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->generatePermission($controller, $action);
        }

        return $this->cache[$key];
    }

    private function generatePermission(string $controller, string $action): Permission
    {
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException) {
            return Permission::failed();
        }

        $guarded = $this->reader->firstFunctionMetadata($method, Guarded::class);
        if (!$guarded instanceof Guarded) {
            return Permission::failed();
        }

        $namespace = $this->reader->firstClassMetadata($method->getDeclaringClass(), GuardNamespace::class);

        if ($guarded->permission || ($namespace instanceof GuardNamespace && $namespace->namespace)) {
            return Permission::ok(
                $this->makePermission($guarded, $method, $namespace),
                $this->mapFailureException($guarded),
                $guarded->errorMessage ?: \sprintf(
                    'Unauthorized access `%s`',
                    $guarded->permission ?: $method->getName()
                )
            );
        }

        throw new InterceptorException(
            \sprintf(
                'Unable to apply @Guarded without name or @GuardNamespace on `%s`->`%s`',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            )
        );
    }

    private function makePermission(Guarded $guarded, \ReflectionMethod $method, ?GuardNamespace $ns): string
    {
        $permission = [];
        if ($this->namespace) {
            $permission[] = $this->namespace;
        }

        if ($ns !== null && $ns->namespace) {
            $permission[] = $ns->namespace;
        }

        $permission[] = $guarded->permission ?: $method->getName();

        return \implode('.', $permission);
    }

    private function mapFailureException(Guarded $guarded): int
    {
        return self::FAILURE_MAP[$guarded->else] ?? ControllerException::FORBIDDEN;
    }
}
