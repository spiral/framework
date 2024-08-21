<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\Internal\ActionResolver;

/**
 * Provides ability to call controllers in IoC scope.
 *
 * Make sure to bind ScopeInterface in your container.
 *
 * @deprecated will be removed in Spiral v4.0
 */
abstract class AbstractCore implements CoreInterface, HandlerInterface
{
    /** @internal */
    protected ResolverInterface $resolver;

    public function __construct(
        /** @internal */
        #[Proxy] protected ContainerInterface $container
    ) {
        // TODO: can we simplify this?
        // resolver is usually the container itself
        /** @psalm-suppress MixedAssignment */
        $this->resolver = $container instanceof ResolverInterface
            ? $container
            : $container
                ->get(InvokerInterface::class)
                ->invoke(static fn (#[Proxy] ResolverInterface $resolver) => $resolver);
    }

    /**
     * @psalm-assert class-string $controller
     * @psalm-assert non-empty-string $action
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        $method = ActionResolver::pathToReflection($controller, $action);

        // Validate method
        ActionResolver::validateControllerMethod($method);

        return $this->invoke(null, $controller, $method, $parameters);
    }

    public function handle(CallContextInterface $context): mixed
    {
        $target = $context->getTarget();
        $reflection = $target->getReflection();
        return $reflection instanceof \ReflectionMethod
            ? $this->invoke($target->getObject(), $target->getPath()[0], $reflection, $context->getArguments())
            : $this->callAction($target->getPath()[0], $target->getPath()[1], $context->getArguments());
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
        return $this->resolver->resolveArguments($method, $parameters);
    }

    /**
     * @throws \Throwable
     */
    private function invoke(?object $object, string $class, \ReflectionMethod $method, array $arguments): mixed
    {
        try {
            $args = $this->resolveArguments($method, $arguments);
        } catch (ArgumentResolvingException | InvalidArgumentException $e) {
            throw new ControllerException(
                \sprintf(
                    'Missing/invalid parameter %s of `%s`->`%s`',
                    $e->getParameter(),
                    $class,
                    $method->getName(),
                ),
                ControllerException::BAD_ARGUMENT,
                $e,
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ControllerException(
                $e->getMessage(),
                ControllerException::ERROR,
                $e,
            );
        }

        return $method->invokeArgs($object ?? $this->container->get($class), $args);
    }
}
