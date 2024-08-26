<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Internal;

use Spiral\Interceptors\Exception\TargetCallException;

/**
 * @internal
 */
final class ActionResolver
{
    /**
     * @psalm-assert class-string $controller
     * @psalm-assert non-empty-string $action
     *
     * @throws TargetCallException
     */
    public static function pathToReflection(string $controller, string $action): \ReflectionMethod
    {
        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            throw new TargetCallException(
                \sprintf('Invalid action `%s`->`%s`', $controller, $action),
                TargetCallException::BAD_ACTION,
                $e
            );
        }

        return $method;
    }

    /**
     * @throws TargetCallException
     * @psalm-assert object|null $controller
     */
    public static function validateControllerMethod(\ReflectionMethod $method, mixed $controller = null): void
    {
        if ($method->isStatic() || !$method->isPublic()) {
            throw new TargetCallException(
                \sprintf(
                    'Invalid action `%s`->`%s`',
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ),
                TargetCallException::BAD_ACTION
            );
        }

        if ($controller === null) {
            return;
        }

        if (!\is_object($controller) || !$method->getDeclaringClass()->isInstance($controller)) {
            throw new TargetCallException(
                \sprintf(
                    'Invalid controller. Expected instance of `%s`, got `%s`.',
                    $method->getDeclaringClass()->getName(),
                    \get_debug_type($controller),
                ),
                TargetCallException::INVALID_CONTROLLER,
            );
        }
    }
}
