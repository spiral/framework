<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Handler;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\Exception\TargetCallException;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\Internal\ActionResolver;

/**
 * Handler resolves missing arguments from the container.
 * It requires the Target to explicitly point to a method or function.
 */
final class AutowireHandler implements HandlerInterface
{
    public function __construct(
        /** @internal */
        protected ContainerInterface $container,
    ) {
    }

    /**
     * @psalm-assert class-string $controller
     * @psalm-assert non-empty-string $action
     * @throws \Throwable
     */
    public function handle(CallContextInterface $context): mixed
    {
        // Resolve controller method
        $method = $context->getTarget()->getReflection() ?? throw new TargetCallException(
            "Reflection not provided for target `{$context->getTarget()}`.",
            TargetCallException::NOT_FOUND,
        );
        $path = $context->getTarget()->getPath();

        if ($method instanceof \ReflectionFunction) {
            return $method->invokeArgs(
                $this->resolveArguments($method, $context)
            );
        }

        if (!$method instanceof \ReflectionMethod) {
            throw new TargetCallException("Action not found for target `{$context->getTarget()}`.");
        }

        $controller = $context->getTarget()->getObject() ?? $this->container->get(\reset($path));

        // Validate method and controller
        ActionResolver::validateControllerMethod($method, $controller);

        // Run action
        return $method->invokeArgs($controller, $this->resolveArguments($method, $context));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function resolveArguments(\ReflectionFunctionAbstract $method, CallContextInterface $context): array
    {
        $resolver = $this->container->get(ResolverInterface::class);
        \assert($resolver instanceof ResolverInterface);

        return $resolver->resolveArguments($method, $context->getArguments());
    }
}
