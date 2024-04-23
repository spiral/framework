<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Handler;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Exception\TargetCallException;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\Internal\ActionResolver;

class ReflectionHandler implements HandlerInterface
{
    /**
     * @param bool $resolveFromPath Try to resolve controller and action reflection from the target path if
     *        reflection is not provided.
     */
    public function __construct(
        /** @internal */
        protected ContainerInterface $container,
        protected bool $resolveFromPath = true,
    ) {
    }

    /**
     * @psalm-assert class-string $controller
     * @psalm-assert non-empty-string $action
     * @throws \Throwable
     */
    public function handle(CallContext $context): mixed
    {
        // Resolve controller method
        $method = $context->getTarget()->getReflection();
        if ($method === null) {
            $this->resolveFromPath or throw new TargetCallException(
                "Reflection not provided for target `{$context->getTarget()}`.",
                TargetCallException::NOT_FOUND,
            );

            $path = $context->getTarget()->getPath();
            if (\count($path) !== 2) {
                throw new TargetCallException(
                    "Invalid target path to resolve reflection for `{$context->getTarget()}`."
                    . ' Expected two parts: class and method.',
                    TargetCallException::NOT_FOUND,
                );
            }

            $method = ActionResolver::pathToReflection(\reset($path), \end($path));
        }

        if ($method instanceof \ReflectionFunction) {
            return $method->invokeArgs(
                $this->resolveArguments($method, $context)
            );
        }

        if (!$method instanceof \ReflectionMethod) {
            throw new TargetCallException("Action not found for target `{$context->getTarget()}`.");
        }

        $controller = $this->container->get($method->getDeclaringClass()->getName());

        // Validate method and controller
        ActionResolver::validateControllerMethod($method, $controller);

        // Run action
        return $method->invokeArgs($controller, $this->resolveArguments($method, $context));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function resolveArguments(\ReflectionFunctionAbstract $method, CallContext $context): array
    {
        $resolver = $this->container->get(ResolverInterface::class);
        \assert($resolver instanceof ResolverInterface);

        return $resolver->resolveArguments($method, $context->getArguments());
    }
}
