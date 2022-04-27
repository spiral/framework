<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use TypeError;

/**
 * Provides ability to call controllers in IoC scope.
 *
 * Make sure to bind ScopeInterface in your container.
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
        $this->resolver = $container->get(ResolverInterface::class);
    }

    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        try {
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
            // getting the set of arguments should be sent to requested method
            $args = $this->resolver->resolveArguments($method, $parameters, validate: true);
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
            static fn () => $method->invokeArgs($container->get($controller), $args)
        );
    }
}
