<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;

/**
 * Provides ability to call controllers in IoC scope.
 *
 * Make sure to bind ScopeInterface in your container.
 *
 * @deprecated
 */
abstract class AbstractCore implements CoreInterface
{
    /** @internal */
    protected ResolverInterface $resolver;

    public function __construct(
        /** @internal */
        protected ContainerInterface $container
    ) {
        // resolver is usually the container itself
        /** @psalm-suppress MixedAssignment */
        $this->resolver = $container->get(ResolverInterface::class);
    }

    /**
     * @psalm-assert class-string $controller
     * @psalm-assert non-empty-string $action
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            throw new ControllerException(
                \sprintf('Invalid action `%s`->`%s`', $controller, $action),
                ControllerException::BAD_ACTION,
                $e
            );
        }

        if ($method->isStatic() || !$method->isPublic()) {
            throw new ControllerException(
                \sprintf('Invalid action `%s`->`%s`', $controller, $action),
                ControllerException::BAD_ACTION
            );
        }

        try {
            $args = $this->resolveArguments($method, $parameters);
        } catch (ArgumentResolvingException|InvalidArgumentException $e) {
            throw new ControllerException(
                \sprintf('Missing/invalid parameter %s of `%s`->`%s`', $e->getParameter(), $controller, $action),
                ControllerException::BAD_ARGUMENT,
                $e
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ControllerException(
                $e->getMessage(),
                ControllerException::ERROR,
                $e
            );
        }

        $container = $this->container;
        return ContainerScope::runScope(
            $container,
            /** @psalm-suppress MixedArgument */
            static fn (): mixed => $method->invokeArgs($container->get($controller), $args)
        );
    }

    protected function resolveArguments(\ReflectionMethod $method, array $parameters): array
    {
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (
                \array_key_exists($name, $parameters) &&
                $parameters[$name] === null &&
                $parameter->isDefaultValueAvailable()
            ) {
                /** @psalm-suppress MixedAssignment */
                $parameters[$name] = $parameter->getDefaultValue();
            }
        }

        // getting the set of arguments should be sent to requested method
        return $this->resolver->resolveArguments($method, $parameters, validate: true);
    }
}
